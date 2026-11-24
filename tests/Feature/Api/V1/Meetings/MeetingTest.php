<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Meetings;

use App\Enums\MeetingAttendeeResponse;
use App\Enums\MeetingRecordingStatus;
use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\Project;
use App\Models\TranscriptSegment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MeetingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_meetings_with_calendar_filters_and_scoping(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Meeting 1: User is organizer (scheduled today)
        $meeting1 = Meeting::factory()->create([
            'organizer_id' => $user->id,
            'title' => 'Sprint Retrospective',
            'starts_at' => now()->startOfDay()->addHours(10),
            'ends_at' => now()->startOfDay()->addHours(11),
            'status' => MeetingStatus::Scheduled,
        ]);

        // Meeting 2: User is attendee (scheduled tomorrow)
        $meeting2 = Meeting::factory()->create([
            'organizer_id' => $otherUser->id,
            'title' => 'Design System Sync',
            'starts_at' => now()->addDay()->startOfDay()->addHours(14),
            'ends_at' => now()->addDay()->startOfDay()->addHours(15),
            'status' => MeetingStatus::Scheduled,
        ]);
        $meeting2->attendees()->attach($user->id, ['response' => MeetingAttendeeResponse::Accepted->value]);

        // Meeting 3: User is NOT involved
        Meeting::factory()->create([
            'organizer_id' => $otherUser->id,
            'title' => 'Secret Executive Meeting',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
        ]);

        Sanctum::actingAs($user);

        // General list: sees meeting1 and meeting2, but not meeting3
        $response = $this->getJson('/api/v1/meetings');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Sprint Retrospective')
            ->assertJsonPath('data.1.title', 'Design System Sync');

        // Calendar date range filter: only today
        $filtered = $this->getJson('/api/v1/meetings?start_date='.now()->startOfDay()->toISOString().'&end_date='.now()->endOfDay()->toISOString());

        $filtered->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Sprint Retrospective');
    }

    public function test_user_can_schedule_meeting_with_attendees(): void
    {
        $organizer = User::factory()->create();
        $attendee1 = User::factory()->create();
        $attendee2 = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $organizer->id]);

        Sanctum::actingAs($organizer);

        $payload = [
            'project_id' => $project->ulid,
            'title' => 'Q4 Roadmap Kickoff',
            'description' => 'Reviewing key deliverables for Q4 platform improvements.',
            'starts_at' => now()->addDays(2)->toIso8601String(),
            'ends_at' => now()->addDays(2)->addHour()->toIso8601String(),
            'meeting_url' => 'https://meet.google.com/xyz-abcd-efg',
            'attendee_ids' => [$attendee1->ulid, $attendee2->ulid],
        ];

        $response = $this->postJson('/api/v1/meetings', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Q4 Roadmap Kickoff')
            ->assertJsonPath('data.status', MeetingStatus::Scheduled->value);

        $this->assertDatabaseHas('meetings', [
            'title' => 'Q4 Roadmap Kickoff',
            'organizer_id' => $organizer->id,
        ]);

        // Organizer should be automatically accepted attendee
        $this->assertDatabaseHas('meeting_attendees', [
            'user_id' => $organizer->id,
            'response' => MeetingAttendeeResponse::Accepted->value,
        ]);

        // Invited attendees should be pending
        $this->assertDatabaseHas('meeting_attendees', [
            'user_id' => $attendee1->id,
            'response' => MeetingAttendeeResponse::Pending->value,
        ]);
    }

    public function test_attendee_can_rsvp_to_meeting(): void
    {
        $organizer = User::factory()->create();
        $attendee = User::factory()->create();

        $meeting = Meeting::factory()->create([
            'organizer_id' => $organizer->id,
        ]);
        $meeting->attendees()->attach($attendee->id, ['response' => MeetingAttendeeResponse::Pending->value]);

        Sanctum::actingAs($attendee);

        $response = $this->postJson("/api/v1/meetings/{$meeting->ulid}/rsvp", [
            'response' => MeetingAttendeeResponse::Accepted->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'RSVP updated successfully.');

        $this->assertDatabaseHas('meeting_attendees', [
            'meeting_id' => $meeting->id,
            'user_id' => $attendee->id,
            'response' => MeetingAttendeeResponse::Accepted->value,
        ]);
    }

    public function test_organizer_can_update_and_delete_meeting(): void
    {
        $organizer = User::factory()->create();
        $meeting = Meeting::factory()->create(['organizer_id' => $organizer->id]);

        Sanctum::actingAs($organizer);

        // Update
        $updateResponse = $this->patchJson("/api/v1/meetings/{$meeting->ulid}", [
            'title' => 'Updated Sprint Meeting',
            'status' => MeetingStatus::Live->value,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.title', 'Updated Sprint Meeting')
            ->assertJsonPath('data.status', MeetingStatus::Live->value);

        // Delete
        $deleteResponse = $this->deleteJson("/api/v1/meetings/{$meeting->ulid}");

        $deleteResponse->assertOk();
        $this->assertSoftDeleted('meetings', ['id' => $meeting->id]);
    }

    public function test_organizer_can_upload_meeting_recording(): void
    {
        Storage::fake('public');

        $organizer = User::factory()->create();
        $meeting = Meeting::factory()->create([
            'organizer_id' => $organizer->id,
            'recording_status' => MeetingRecordingStatus::None,
        ]);

        Sanctum::actingAs($organizer);

        $fakeFile = UploadedFile::fake()->create('standup-recording.mp4', 2048, 'video/mp4');

        $response = $this->postJson("/api/v1/meetings/{$meeting->ulid}/recording", [
            'file' => $fakeFile,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.recording_status', MeetingRecordingStatus::Ready->value);

        $meeting->refresh();
        $this->assertEquals(MeetingRecordingStatus::Ready, $meeting->recording_status);
        $this->assertNotEmpty($meeting->getFirstMediaUrl('recording'));
    }

    public function test_can_add_and_search_transcript_segments(): void
    {
        $organizer = User::factory()->create();
        $speaker1 = User::factory()->create(['name' => 'Mohamed Salah']);
        $speaker2 = User::factory()->create(['name' => 'Ahmed Hassan']);

        $meeting = Meeting::factory()->create(['organizer_id' => $organizer->id]);

        Sanctum::actingAs($organizer);

        // Add transcript segments
        $addResponse = $this->postJson("/api/v1/meetings/{$meeting->ulid}/transcripts", [
            'segments' => [
                [
                    'speaker_id' => $speaker1->ulid,
                    'start_second' => 0,
                    'text' => 'Welcome everyone. Today we are reviewing the homepage UI wireframes.',
                    'language' => 'en',
                ],
                [
                    'speaker_id' => $speaker2->ulid,
                    'start_second' => 45,
                    'text' => 'I have updated the sign-up flow button contrast ratios.',
                    'language' => 'en',
                ],
            ],
        ]);

        $addResponse->assertStatus(201);
        $this->assertDatabaseCount('transcript_segments', 2);

        // Retrieve transcript
        $listResponse = $this->getJson("/api/v1/meetings/{$meeting->ulid}/transcripts");
        $listResponse->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.start_second', 0)
            ->assertJsonPath('data.0.speaker.name', 'Mohamed Salah');

        // Search transcript
        $searchResponse = $this->getJson("/api/v1/meetings/{$meeting->ulid}/transcripts?search=contrast");
        $searchResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.speaker.name', 'Ahmed Hassan');
    }

    public function test_can_generate_and_retrieve_ai_meeting_summary(): void
    {
        $organizer = User::factory()->create();
        $speaker = User::factory()->create(['name' => 'Mohanad Wahib']);

        $meeting = Meeting::factory()->create(['organizer_id' => $organizer->id]);

        TranscriptSegment::factory()->create([
            'meeting_id' => $meeting->id,
            'speaker_id' => $speaker->id,
            'start_second' => 10,
            'text' => 'The team reviewed the latest homepage design and agreed on the sign-up flow wireframes.',
        ]);

        Sanctum::actingAs($organizer);

        // Generate AI Summary
        $genResponse = $this->postJson("/api/v1/meetings/{$meeting->ulid}/summaries/generate");

        $genResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'language',
                    'key_points',
                    'action_items',
                    'generated_at',
                ],
            ]);

        $this->assertNotEmpty($genResponse->json('data.key_points'));
        $this->assertNotEmpty($genResponse->json('data.action_items'));

        // Retrieve Summary
        $getResponse = $this->getJson("/api/v1/meetings/{$meeting->ulid}/summary");
        $getResponse->assertOk()
            ->assertJsonPath('data.language', 'en');
    }

    public function test_collaborative_notes_can_be_created_and_managed(): void
    {
        $organizer = User::factory()->create();
        $member = User::factory()->create();
        $stranger = User::factory()->create();

        $meeting = Meeting::factory()->create(['organizer_id' => $organizer->id]);
        $meeting->attendees()->attach($member->id);

        Sanctum::actingAs($member);

        // Create Note
        $createResponse = $this->postJson("/api/v1/meetings/{$meeting->ulid}/notes", [
            'body' => 'Remember to follow up on the typography hierarchy.',
        ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.body', 'Remember to follow up on the typography hierarchy.')
            ->assertJsonPath('data.user.name', $member->name);

        $noteId = $createResponse->json('data.id');

        // Update Note via same endpoint (idempotent)
        $updateResponse = $this->postJson("/api/v1/meetings/{$meeting->ulid}/notes", [
            'body' => 'Remember to follow up on the typography hierarchy and color contrast.',
        ]);

        $updateResponse->assertStatus(201)
            ->assertJsonPath('data.body', 'Remember to follow up on the typography hierarchy and color contrast.');

        // Stranger cannot delete note
        Sanctum::actingAs($stranger);
        $forbiddenDelete = $this->deleteJson("/api/v1/meetings/notes/{$noteId}");
        $forbiddenDelete->assertStatus(403);

        // Author can delete note
        Sanctum::actingAs($member);
        $deleteResponse = $this->deleteJson("/api/v1/meetings/notes/{$noteId}");
        $deleteResponse->assertOk();

        $this->assertDatabaseMissing('meeting_notes', ['id' => $noteId]);
    }
}

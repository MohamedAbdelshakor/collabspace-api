<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\ConversationType;
use App\Enums\MeetingAttendeeResponse;
use App\Enums\MeetingRecordingStatus;
use App\Enums\MeetingStatus;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\DailyMetric;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\MeetingSummary;
use App\Models\Message;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectProgressSnapshot;
use App\Models\Task;
use App\Models\TimeLog;
use App\Models\TranscriptSegment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class FigmaDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create main Figma user: Mohamed Salah
        $salah = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Mohamed Salah',
                'password' => Hash::make('password'),
                'job_title' => 'Senior UI/UX Designer',
                'years_of_experience' => 6,
                'phone_country' => 'EG',
                'phone_number' => '+201234569878',
                'bio' => 'Experienced UI/UX Designer with over 5 years of expertise in creating user-centered digital experiences. Passionate about design systems, accessibility, and leading creative teams to deliver exceptional products.',
                'availability_status' => AvailabilityStatus::Available,
                'timezone' => 'Africa/Cairo',
                'locale' => 'en',
                'created_at' => Carbon::create(2022, 4, 1),
            ]
        );
        $salah->assignRole('admin');

        // 2. Create Figma Teammates
        $teammatesData = [
            ['name' => 'Mohanad wahib', 'email' => 'mohanad@example.com', 'job_title' => 'UI/UX Lead'],
            ['name' => 'Sara Hassan', 'email' => 'sara@example.com', 'job_title' => 'UI Designer'],
            ['name' => 'Ali Mohamed', 'email' => 'ali.m@example.com', 'job_title' => 'Frontend Lead'],
            ['name' => 'Khalid Mousa', 'email' => 'khalid@example.com', 'job_title' => 'React Dev'],
            ['name' => 'Mahmoud Salah', 'email' => 'mahmoud@example.com', 'job_title' => 'Backend Lead'],
            ['name' => 'Ahmed Mohamed', 'email' => 'ahmed.m@example.com', 'job_title' => 'API Engineer'],
            ['name' => 'Ali Hassan', 'email' => 'ali.hassan@example.com', 'job_title' => 'Code Reviewer'],
            ['name' => 'Ahmed Hassan', 'email' => 'ahmed.h@example.com', 'job_title' => 'Product Designer'],
            ['name' => 'Wahib salem', 'email' => 'wahib@example.com', 'job_title' => 'Frontend Dev'],
            ['name' => 'Mohamed Nadir', 'email' => 'nadir.m@example.com', 'job_title' => 'Database Engineer'],
            ['name' => 'Omar Khaled', 'email' => 'omar@example.com', 'job_title' => 'UX Researcher'],
            ['name' => 'Mahmoud Ashraf', 'email' => 'ashraf@example.com', 'job_title' => 'UI Designer'],
            ['name' => 'Nadir Mustafa', 'email' => 'nadir.mustafa@example.com', 'job_title' => 'Project Manager'],
            ['name' => 'Salah Ali', 'email' => 'salah.ali@example.com', 'job_title' => 'Frontend Dev'],
            ['name' => 'Ramzy Khaled', 'email' => 'ramzy@example.com', 'job_title' => 'Marketing Specialist'],
            ['name' => 'Sohaib Rady', 'email' => 'sohaib@example.com', 'job_title' => 'QA Engineer'],
        ];

        $users = [];
        foreach ($teammatesData as $t) {
            $user = User::updateOrCreate(
                ['email' => $t['email']],
                [
                    'name' => $t['name'],
                    'password' => Hash::make('password'),
                    'job_title' => $t['job_title'],
                    'years_of_experience' => 4,
                    'phone_country' => 'EG',
                    'availability_status' => AvailabilityStatus::Available,
                    'timezone' => 'Africa/Cairo',
                    'locale' => 'en',
                ]
            );
            $user->assignRole('member');
            $users[$t['name']] = $user;
        }

        // 3. Departments
        $uiDept = Department::where('slug', 'ui-ux')->first();
        $feDept = Department::where('slug', 'frontend')->first();
        $beDept = Department::where('slug', 'backend')->first();
        $mktDept = Department::where('slug', 'marketing')->first();

        // 4. Projects: Alpha and SepetGo
        $alpha = Project::updateOrCreate(
            ['name' => 'Alpha'],
            [
                'owner_id' => $salah->id,
                'description' => 'This project focuses on organizing tasks, tracking progress, and coordinating team efforts to ensure timely delivery and clear visibility across all project activities.',
                'type' => ProjectType::SaaS,
                'priority' => ProjectPriority::High,
                'status' => ProjectStatus::Active,
                'start_date' => '2025-10-12',
                'deadline' => '2025-12-25',
                'progress' => 42,
            ]
        );

        $sepetGo = Project::updateOrCreate(
            ['name' => 'SepetGo'],
            [
                'owner_id' => $salah->id,
                'description' => 'E-commerce marketplace and order delivery logistics coordination system.',
                'type' => ProjectType::WebApp,
                'priority' => ProjectPriority::Medium,
                'status' => ProjectStatus::Active,
                'start_date' => '2025-12-12',
                'deadline' => '2025-12-14',
                'progress' => 43,
            ]
        );

        $salah->update(['primary_project_id' => $alpha->id]);

        // 5. Project Members for Alpha
        $membersData = [
            ['user' => $users['Mohanad wahib'], 'dept' => $uiDept, 'role' => 'Leader', 'leader' => true],
            ['user' => $salah, 'dept' => $uiDept, 'role' => 'Senior UI/UX Designer', 'leader' => false],
            ['user' => $users['Sara Hassan'], 'dept' => $uiDept, 'role' => 'UI Designer', 'leader' => false],
            ['user' => $users['Ahmed Hassan'], 'dept' => $uiDept, 'role' => 'Product Designer', 'leader' => false],
            ['user' => $users['Ali Mohamed'], 'dept' => $feDept, 'role' => 'Frontend Leader', 'leader' => true],
            ['user' => $users['Khalid Mousa'], 'dept' => $feDept, 'role' => 'React Dev', 'leader' => false],
            ['user' => $users['Wahib salem'], 'dept' => $feDept, 'role' => 'UI Engineer', 'leader' => false],
            ['user' => $users['Mahmoud Salah'], 'dept' => $beDept, 'role' => 'Backend Leader', 'leader' => true],
            ['user' => $users['Ahmed Mohamed'], 'dept' => $beDept, 'role' => 'API Engineer', 'leader' => false],
            ['user' => $users['Mohamed Nadir'], 'dept' => $beDept, 'role' => 'Database Engineer', 'leader' => false],
            ['user' => $users['Ramzy Khaled'], 'dept' => $mktDept, 'role' => 'Marketing Lead', 'leader' => true],
        ];

        foreach ($membersData as $m) {
            ProjectMember::updateOrCreate(
                ['project_id' => $alpha->id, 'user_id' => $m['user']->id],
                [
                    'department_id' => $m['dept']?->id,
                    'role_title' => $m['role'],
                    'is_leader' => $m['leader'],
                    'joined_at' => now()->subMonths(2),
                ]
            );
        }

        // 6. Tasks for Alpha (Matching Figma Kanban board)
        $tasksData = [
            // To-do
            [
                'title' => 'Creat Wireframes',
                'description' => 'Initial layout and structure for main page',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::High,
                'progress' => 0,
                'position' => 1,
                'due_date' => '2025-12-12',
                'assignees' => [$users['Mohanad wahib'], $users['Sara Hassan']],
            ],
            [
                'title' => 'User Research Analysis',
                'description' => 'Initial layout and structure for main page',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Medium,
                'progress' => 0,
                'position' => 2,
                'due_date' => '2025-12-18',
                'assignees' => [$users['Mohanad wahib']],
            ],
            [
                'title' => 'Design System Updates',
                'description' => 'Update component library with new brand color',
                'status' => TaskStatus::Todo,
                'priority' => TaskPriority::Low,
                'progress' => 0,
                'position' => 3,
                'due_date' => '2025-12-20',
                'assignees' => [$salah],
            ],
            // In Progress
            [
                'title' => 'Chat Screen Prototyping',
                'description' => 'Create Interactive Prototype for Chat screen',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::High,
                'progress' => 62,
                'position' => 1,
                'due_date' => '2025-12-12',
                'assignees' => [$users['Mohanad wahib'], $users['Ahmed Hassan']],
            ],
            [
                'title' => 'Create Miro interaction',
                'description' => 'Initial layout and structure for main page',
                'status' => TaskStatus::InProgress,
                'priority' => TaskPriority::Low,
                'progress' => 32,
                'position' => 2,
                'due_date' => '2025-12-12',
                'assignees' => [$users['Mohanad wahib']],
            ],
            // In Review
            [
                'title' => 'Mobile Responsiveness',
                'description' => 'Create Interactive Prototype for Chat screen',
                'status' => TaskStatus::InReview,
                'priority' => TaskPriority::High,
                'progress' => 85,
                'position' => 1,
                'due_date' => '2025-12-12',
                'reviewer' => $users['Ali Hassan'],
                'assignees' => [$users['Mohanad wahib'], $users['Khalid Mousa']],
            ],
            [
                'title' => 'Accessibility Audit',
                'description' => 'Initial layout and structure for main page',
                'status' => TaskStatus::InReview,
                'priority' => TaskPriority::Low,
                'progress' => 70,
                'position' => 2,
                'due_date' => '2025-12-12',
                'reviewer' => $users['Ali Hassan'],
                'assignees' => [$users['Mohanad wahib']],
            ],
            // Completed
            [
                'title' => 'Login Screen',
                'description' => 'Create Interactive Prototype for Chat screen',
                'status' => TaskStatus::Completed,
                'priority' => TaskPriority::High,
                'progress' => 100,
                'position' => 1,
                'due_date' => '2025-12-12',
                'completed_at' => now()->subDays(2),
                'assignees' => [$salah, $users['Sara Hassan']],
            ],
            [
                'title' => 'Survey',
                'description' => 'Initial layout and structure for main page',
                'status' => TaskStatus::Completed,
                'priority' => TaskPriority::Low,
                'progress' => 100,
                'position' => 2,
                'due_date' => '2025-12-12',
                'completed_at' => now()->subDay(),
                'assignees' => [$users['Mohanad wahib']],
            ],
        ];

        foreach ($tasksData as $tData) {
            $task = Task::updateOrCreate(
                ['project_id' => $alpha->id, 'title' => $tData['title']],
                [
                    'created_by' => $salah->id,
                    'reviewer_id' => $tData['reviewer']?->id ?? null,
                    'description' => $tData['description'],
                    'status' => $tData['status'],
                    'priority' => $tData['priority'],
                    'progress' => $tData['progress'],
                    'position' => $tData['position'],
                    'start_date' => '2025-10-12',
                    'due_date' => $tData['due_date'],
                    'completed_at' => $tData['completed_at'] ?? null,
                ]
            );

            if (isset($tData['assignees'])) {
                $assigneeIds = array_map(fn ($u) => $u->id, $tData['assignees']);
                $task->assignees()->syncWithoutDetaching($assigneeIds);
            }

            // Seed 8 comments for each task (represented by the 8 icon in Figma)
            for ($c = 1; $c <= 8; $c++) {
                Comment::firstOrCreate([
                    'commentable_type' => Task::class,
                    'commentable_id' => $task->id,
                    'user_id' => $salah->id,
                    'body' => "Comment {$c} regarding {$task->title} progress and alignment.",
                ]);
            }
        }

        // 7. Time Logs for Mohamed Salah: totaling 345 hours (20,700 minutes) as shown on Figma Profile
        $firstTask = Task::where('project_id', $alpha->id)->first();
        if ($firstTask) {
            TimeLog::firstOrCreate(
                ['task_id' => $firstTask->id, 'user_id' => $salah->id, 'logged_on' => now()->subDay()->toDateString()],
                ['minutes' => 345 * 60, 'note' => 'Consolidated product design and sprint deliverables']
            );
        }

        // 8. Conversations & Messages
        // Channels
        $alphaPublic = Conversation::firstOrCreate(
            ['direct_key' => 'channel:project:'.$alpha->id],
            [
                'type' => ConversationType::Channel,
                'project_id' => $alpha->id,
                'name' => 'UI Dashboard Project Public Channel',
            ]
        );

        $feChannel = Conversation::firstOrCreate(
            ['direct_key' => 'channel:project:'.$alpha->id.':dept:'.$feDept?->id],
            [
                'type' => ConversationType::Channel,
                'project_id' => $alpha->id,
                'department_id' => $feDept?->id,
                'name' => 'Frontend Team Channel',
            ]
        );

        $beChannel = Conversation::firstOrCreate(
            ['direct_key' => 'channel:project:'.$alpha->id.':dept:'.$beDept?->id],
            [
                'type' => ConversationType::Channel,
                'project_id' => $alpha->id,
                'department_id' => $beDept?->id,
                'name' => 'Backend Team Channel',
            ]
        );

        // Direct Conversation: Salah with Mohanad
        $directKey = min($salah->id, $users['Mohanad wahib']->id).':'.max($salah->id, $users['Mohanad wahib']->id);
        $directChat = Conversation::firstOrCreate(
            ['direct_key' => $directKey],
            [
                'type' => ConversationType::Direct,
                'name' => 'Mohamed Ahmed',
            ]
        );
        $directChat->participants()->syncWithoutDetaching([$salah->id, $users['Mohanad wahib']->id]);

        // Channel Messages matching Figma screenshot
        $m1 = Message::firstOrCreate([
            'conversation_id' => $alphaPublic->id,
            'user_id' => $users['Mohanad wahib']->id,
            'body' => 'Yes,we can do this and edit screen',
        ]);

        $m2 = Message::firstOrCreate([
            'conversation_id' => $alphaPublic->id,
            'user_id' => $users['Salah Ali']->id,
            'body' => 'You should do it early',
        ]);

        // Thread Parent Message
        $threadParent = Message::firstOrCreate([
            'conversation_id' => $alphaPublic->id,
            'user_id' => $users['Mohanad wahib']->id,
            'thread_title' => '#Screen edit',
            'body' => 'I edit it and now you can see it #Screen edit',
            'replies_count' => 2,
        ]);

        Message::firstOrCreate([
            'conversation_id' => $alphaPublic->id,
            'parent_id' => $threadParent->id,
            'user_id' => $salah->id,
            'body' => 'Reviewing the wireframe edits now. Looks sharp!',
        ]);

        // 9. Meetings & Transcript & AI Summary
        $designReview = Meeting::firstOrCreate(
            ['title' => 'Design Review'],
            [
                'project_id' => $alpha->id,
                'organizer_id' => $users['Mohanad wahib']->id,
                'description' => 'Reviewing the latest design updates and aligning on next steps.',
                'starts_at' => now()->setTime(14, 0),
                'ends_at' => now()->setTime(15, 0),
                'status' => MeetingStatus::Scheduled,
                'recording_status' => MeetingRecordingStatus::Ready,
            ]
        );

        // Attendees
        $attendees = [
            $users['Mohanad wahib'],
            $users['Ahmed Hassan'],
            $users['Omar Khaled'],
            $users['Mahmoud Ashraf'],
            $users['Nadir Mustafa'],
        ];
        foreach ($attendees as $att) {
            MeetingAttendee::firstOrCreate([
                'meeting_id' => $designReview->id,
                'user_id' => $att->id,
            ], ['response' => MeetingAttendeeResponse::Accepted]);
        }

        // Transcripts verbatim from Figma screenshot
        $transcript = [
            ['speaker' => $users['Mohanad wahib'], 'time' => 1, 'text' => "Good morning everyone. Thanks for joining on time. Today we'll focus on reviewing the latest design updates and aligning on next steps."],
            ['speaker' => $users['Ahmed Hassan'], 'time' => 40, 'text' => 'Morning. I went through the new homepage design, and overall it looks cleaner, but I think we still have some usability issues to address.'],
            ['speaker' => $users['Omar Khaled'], 'time' => 83, 'text' => "I agree. The layout is better, but the call-to-action isn't very clear, especially for first-time users."],
            ['speaker' => $users['Mohanad wahib'], 'time' => 121, 'text' => 'From a UX perspective, the sign-up flow also feels a bit long. Users might drop off before completing the process.'],
            ['speaker' => $users['Mahmoud Ashraf'], 'time' => 288, 'text' => "That's a good point. Do you think we should reduce the number of required fields in the first step?"],
            ['speaker' => $users['Nadir Mustafa'], 'time' => 375, 'text' => "Good morning everyone. Let's begin today's meeting by reviewing the project timeline and assigning tasks for the next phase."],
        ];

        foreach ($transcript as $tr) {
            TranscriptSegment::firstOrCreate([
                'meeting_id' => $designReview->id,
                'speaker_id' => $tr['speaker']->id,
                'start_second' => $tr['time'],
            ], ['language' => 'en', 'text' => $tr['text']]);
        }

        // AI Summary Key points verbatim from Figma screenshot
        MeetingSummary::updateOrCreate(
            ['meeting_id' => $designReview->id, 'language' => 'en'],
            [
                'key_points' => [
                    'The team reviewed the latest homepage design and agreed on the proposed layout.',
                    'Several usability improvements were suggested for the sign-up flow.',
                    'Final color decisions were postponed to the next meeting.',
                    'The design team was asked to update the wireframes based on feedback.',
                    'A follow-up review meeting was scheduled for next week.',
                ],
                'action_items' => [
                    'Update homepage CTA contrast ratio (Assigned to Mohanad wahib)',
                    'Shorten sign-up wizard steps from 4 to 2 (Assigned to Ahmed Hassan)',
                    'Schedule follow-up review for next Tuesday 14:00 (Assigned to Nadir Mustafa)',
                ],
                'generated_at' => now(),
            ]
        );

        // 10. Daily Metrics & Snapshots for Reports
        DailyMetric::updateOrCreate(
            ['date' => now()->toDateString(), 'scope_type' => 'project', 'scope_id' => $alpha->id, 'metric' => 'completion_rate'],
            ['value' => 42.00]
        );

        ProjectProgressSnapshot::updateOrCreate(
            ['project_id' => $alpha->id, 'date' => now()->toDateString()],
            ['progress' => 42]
        );
    }
}

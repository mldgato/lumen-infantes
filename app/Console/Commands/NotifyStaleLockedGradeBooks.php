<?php

namespace App\Console\Commands;

use App\Models\GradeBook;
use App\Notifications\GradeBookPendingReview;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class NotifyStaleLockedGradeBooks extends Command
{
    protected $signature = 'gradebooks:notify-stale {--days=2 : Minimum days locked before notifying}';

    protected $description = 'Notify admins about grade books that have been locked for too long without review';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $staleBooks = GradeBook::with([
            'assignment.pensumCourse.course',
            'assignment.classroom.grade',
            'assignment.classroom.section',
        ])
            ->where('status', 'locked')
            ->where('updated_at', '<', now()->subDays($days))
            ->get();

        if ($staleBooks->isEmpty()) {
            $this->info('No stale locked grade books found.');

            return self::SUCCESS;
        }

        $permission = Permission::where('name', 'admin.grade-books.approve')->first();

        if (! $permission) {
            $this->warn('Permission admin.grade-books.approve not found.');

            return self::FAILURE;
        }

        $recipients = $permission->users()->get();

        if ($recipients->isEmpty()) {
            $this->info('No users with admin.grade-books.approve permission.');

            return self::SUCCESS;
        }

        $notified = 0;

        foreach ($staleBooks as $gradeBook) {
            $daysPending = (int) now()->diffInDays($gradeBook->updated_at);

            foreach ($recipients as $user) {
                $user->notify(new GradeBookPendingReview($gradeBook, $daysPending));
                $notified++;
            }
        }

        $this->info("Sent {$notified} notification(s) for {$staleBooks->count()} stale grade book(s).");

        return self::SUCCESS;
    }
}

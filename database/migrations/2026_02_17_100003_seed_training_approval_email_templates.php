<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $templates = [
        [
            'key' => 'training_submitted',
            'name' => 'Training Submitted for Approval',
            'description' => 'Sent to admin when a trainer submits a training for approval.',
            'subject' => 'New Training Submitted: {{training_title}}',
            'greeting' => 'Hello Admin,',
            'body' => "A trainer has submitted a new training for your review.\n\nTrainer: {{trainer_name}}\nTraining: {{training_title}}\nDate: {{training_date}}\n\nPlease review and approve or deny this training.",
            'action_text' => 'Review Training',
            'action_url' => '/admin/trainings/{{training_id}}/edit',
            'outro' => null,
            'available_variables' => '["trainer_name","training_title","training_date","training_id"]',
        ],
        [
            'key' => 'training_approved',
            'name' => 'Training Approved',
            'description' => 'Sent to the trainer when their training is approved by an admin.',
            'subject' => 'Your Training Has Been Approved: {{training_title}}',
            'greeting' => 'Hello {{trainer_name}},',
            'body' => "Great news! Your training has been approved and is now published.\n\nTraining: {{training_title}}\nDate: {{training_date}}\n\nMembers can now find and register for your training.",
            'action_text' => null,
            'action_url' => null,
            'outro' => 'Thank you for contributing to the NADA community!',
            'available_variables' => '["trainer_name","training_title","training_date"]',
        ],
        [
            'key' => 'training_denied',
            'name' => 'Training Denied',
            'description' => 'Sent to the trainer when their training is denied by an admin.',
            'subject' => 'Training Not Approved: {{training_title}}',
            'greeting' => 'Hello {{trainer_name}},',
            'body' => "Your training submission has not been approved.\n\nTraining: {{training_title}}\nReason: {{denied_reason}}\n\nYou may edit your training and resubmit it for review.",
            'action_text' => null,
            'action_url' => null,
            'outro' => 'If you have questions, please contact the NADA team.',
            'available_variables' => '["trainer_name","training_title","denied_reason"]',
        ],
        [
            'key' => 'group_training_invite',
            'name' => 'Group Training Invitation',
            'description' => 'Sent to invitees when a group training is approved.',
            'subject' => "You're Invited: {{training_title}}",
            'greeting' => 'Hello,',
            'body' => "You have been invited to a NADA training.\n\nTraining: {{training_title}}\nTrainer: {{trainer_name}}\nDate: {{training_date}}\nLocation: {{training_location}}\n\nPlease click the link below to view details and register.",
            'action_text' => 'View Training',
            'action_url' => '/trainings/{{training_id}}?token={{token}}',
            'outro' => 'You must create an account or log in with this email address to register.',
            'available_variables' => '["training_title","trainer_name","training_date","training_location","training_id","token"]',
        ],
    ];

    public function up(): void
    {
        foreach ($this->templates as $template) {
            if (DB::table('email_templates')->where('key', $template['key'])->exists()) {
                continue;
            }

            DB::table('email_templates')->insert(array_merge($template, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        $keys = array_column($this->templates, 'key');
        DB::table('email_templates')->whereIn('key', $keys)->delete();
    }
};

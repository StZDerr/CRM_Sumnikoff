<?php

use App\Models\AccountCredential;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Models\UserNotification;

test('admins receive notification when a user views more than 3 unique credentials within an hour', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $actor = User::factory()->create(['role' => User::ROLE_MARKETER]);

    $org = Organization::create(['name_full' => 'Test Org']);
    $project = Project::factory()->create(['organization_id' => $org->id, 'created_by' => $actor->id]);

    // создаём 4 разных доступa
    $creds = [];
    for ($i = 0; $i < 4; $i++) {
        $creds[] = AccountCredential::create([
            'user_id' => $actor->id,
            'organization_id' => $org->id,
            'project_id' => $project->id,
            'type' => 'other',
            'name' => "Cred {$i}",
            'login' => "user{$i}",
            'password' => 'secret',
            'status' => 'active',
        ]);
    }

    // пользователь просматривает по одному уникальному доступу — 4 раза
    foreach ($creds as $cred) {
        $this
            ->actingAs($actor)
            ->post(route('account-credentials.accessLog', $cred), ['action' => 'view'])
            ->assertStatus(200);
    }

    // администратору должно прийти уведомление (type = credential_views_alert)
    $this->assertTrue(
        UserNotification::where('user_id', $admin->id)
            ->where('actor_id', $actor->id)
            ->where('type', 'credential_views_alert')
            ->exists()
    );
});

<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_type_filter_filters_projects()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $paid = Project::factory()->create(['title' => 'Commercial', 'payment_type' => Project::PAYMENT_TYPE_PAID]);
        $barter = Project::factory()->create(['title' => 'Barter', 'payment_type' => Project::PAYMENT_TYPE_BARTER]);
        $own = Project::factory()->create(['title' => 'Own', 'payment_type' => Project::PAYMENT_TYPE_OWN]);

        $this->actingAs($admin)
            ->get(route('projects.index', ['payment_type' => 'barter']))
            ->assertSeeText('Barter')
            ->assertDontSeeText('Commercial')
            ->assertDontSeeText('Own');

        $this->actingAs($admin)
            ->get(route('projects.index', ['payment_type' => 'own']))
            ->assertSeeText('Own')
            ->assertDontSeeText('Commercial')
            ->assertDontSeeText('Barter');

        $this->actingAs($admin)
            ->get(route('projects.index', ['payment_type' => 'paid']))
            ->assertSeeText('Commercial')
            ->assertDontSeeText('Barter')
            ->assertDontSeeText('Own');
    }

    public function test_marketer_default_sorting_by_payment_due_day_asc()
    {
        $marketer = User::factory()->create(['role' => User::ROLE_MARKETER]);

        $p1 = Project::factory()->create(["title" => 'Due-5',  'marketer_id' => $marketer->id, 'payment_due_day' => 5]);
        $p2 = Project::factory()->create(["title" => 'Due-15', 'marketer_id' => $marketer->id, 'payment_due_day' => 15]);
        $p3 = Project::factory()->create(["title" => 'Due-30', 'marketer_id' => $marketer->id, 'payment_due_day' => 30]);

        $res = $this->actingAs($marketer)->get(route('projects.index'));
        $body = $res->getContent();

        $pos1 = strpos($body, 'Due-5');
        $pos2 = strpos($body, 'Due-15');
        $pos3 = strpos($body, 'Due-30');

        $this->assertNotFalse($pos1);
        $this->assertNotFalse($pos2);
        $this->assertNotFalse($pos3);
        $this->assertTrue($pos1 < $pos2 && $pos2 < $pos3, 'Projects are not ordered by payment_due_day asc for marketer');
    }
}

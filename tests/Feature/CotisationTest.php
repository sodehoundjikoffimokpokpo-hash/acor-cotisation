<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Carnet;
use App\Models\Contribution;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CotisationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the business rule for days covered:
     * Nombre de jours couverts = Montant total versé / Cotisation journalière fixée.
     */
    public function test_days_covered_calculation(): void
    {
        // 1. Create a collector
        $collector = User::create([
            'name' => 'Collector',
            'firstname' => 'Test',
            'username' => 'col_test',
            'password' => bcrypt('password'),
            'role' => 'collector',
        ]);

        // 2. Create client (daily: 100 FCFA, target: 1000 FCFA)
        $client = Client::create([
            'name' => 'Client',
            'firstname' => 'Test',
            'phone' => '+22890000000',
            'address' => 'Lome',
            'daily_contribution' => 100.00,
            'target_amount' => 1000.00,
            'desired_article' => 'Radio',
            'status' => 'active',
            'collector_id' => $collector->id,
        ]);

        // Create associated carnet
        Carnet::create([
            'client_id' => $client->id,
            'number' => 'CARNET-TEST',
            'status' => 'active',
        ]);

        // 3. Record a contribution of 600 FCFA
        Contribution::create([
            'client_id' => $client->id,
            'collector_id' => $collector->id,
            'amount' => 600.00,
            'payment_date' => now()->toDateString(),
            'validation_status' => 'pending',
        ]);

        // 4. Verify totalPaid() and daysCovered()
        $this->assertEquals(600.00, $client->totalPaid());
        $this->assertEquals(6, $client->daysCovered());
        $this->assertEquals("6 jours", $client->contributionTimeFormatted());
    }

    /**
     * Test target goal reaching and status change.
     */
    public function test_goal_status_reached(): void
    {
        // 1. Create collector and client (target 1000 FCFA, daily 100)
        $collector = User::create([
            'name' => 'Collector',
            'firstname' => 'Test',
            'username' => 'col_test',
            'password' => bcrypt('password'),
            'role' => 'collector',
        ]);

        $client = Client::create([
            'name' => 'Client',
            'firstname' => 'Test',
            'phone' => '+22890000000',
            'address' => 'Lome',
            'daily_contribution' => 100.00,
            'target_amount' => 1000.00,
            'desired_article' => 'Radio',
            'status' => 'active',
            'collector_id' => $collector->id,
        ]);

        Carnet::create([
            'client_id' => $client->id,
            'number' => 'CARNET-TEST',
            'status' => 'active',
        ]);

        // 2. Add payment meeting the target
        Contribution::create([
            'client_id' => $client->id,
            'collector_id' => $collector->id,
            'amount' => 1000.00,
            'payment_date' => now()->toDateString(),
            'validation_status' => 'validated',
        ]);

        // 3. Trigger check
        $client->checkGoalStatus();

        // 4. Verify client status changed to 'goal_reached'
        $client->refresh();
        $this->assertEquals('goal_reached', $client->status);
    }

    public function test_employee_permissions_and_gating()
    {
        $admin = User::create([
            'name' => 'Admin',
            'firstname' => 'Test',
            'username' => 'admin-test-perm',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        // 1. Create a collaborator
        $response = $this->post(route('admin.rights.store-collaborator'), [
            'name' => 'COLLAB',
            'firstname' => 'Assistant',
            'username' => 'assistant-test',
            'password' => 'password123',
            'role' => 'employee',
            'title' => 'Adjoint de Direction',
            'can_validate_contributions' => 'on',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        $employee = User::where('username', 'assistant-test')->first();
        $this->assertNotNull($employee);
        $this->assertEquals('employee', $employee->role);
        $this->assertEquals('Adjoint de Direction', $employee->activity_zone);
        $this->assertTrue($employee->can_validate_contributions);
        $this->assertFalse($employee->can_manage_collectors);

        // 2. Act as employee and test routing
        $this->actingAs($employee);

        // Can access employee dashboard
        $this->get(route('employee.dashboard'))->assertStatus(200);

        // Can access validation (permitted)
        $this->get(route('admin.validation'))->assertStatus(200);

        // Cannot access collectors management (not permitted)
        $this->get(route('admin.collectors'))->assertStatus(302); // Redirect back with error
    }
}

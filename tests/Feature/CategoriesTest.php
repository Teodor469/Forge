<?php

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});


test('user can successfully update their own category', function() {
    $user = User::factory()->create();
    
    // Act as the user first, then create the category
    $this->actingAs($user, 'sanctum');
    
    $category = Category::create([
        'name' => 'Test Category',
        'type' => CategoryType::Expense,
        'color' => '#ff0000',
        'icon' => 'test-icon'
    ]);

    $response = $this->putJson("/api/category/user-categories/update/{$category->id}", [
        'name' => 'Updated Category Name',
        'color' => '#00ff00'
    ]);

    $response->assertStatus(201)
    ->assertJson(['message' => 'Category updated successfully!'])
    ->assertJsonStructure([
            'category' => [
                'id', 'name', 'type', 'color', 'icon'
            ]
        ]);

    $category->refresh();
    expect($category->name)->toBe('Updated Category Name');
    expect($category->color)->toBe('#00ff00');
    expect($category->user_id)->toBe($user->id);
});
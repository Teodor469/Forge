<?php

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

//! Test index method
test('test user can see all their categories', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category1 = Category::factory()->create([
        'user_id' => $user1->id,
        'name' => 'A unique name',
    ]);

    $child1 = Category::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Unique subcategory',
        'parent_id' => $category1->id,
    ]);

    $category2 = Category::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Another unique name',
    ]);

    $child2 = Category::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Another unique subcategory',
        'parent_id' => $category2->id,
    ]);

    $response = $this->actingAs($user1)->getJson('/api/category/user-categories');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'categories' => [
                '*' => [
                    'id',
                    'name',
                    'type',
                    'color',
                    'icon',
                    'parent_id',
                    'children' => [
                        '*' => [
                            'id',
                            'name',
                            'type',
                            'color',
                            'icon',
                            'parent_id'
                        ]
                    ]
                ]
            ]
        ])
        ->assertJson([
            'message' => 'Successfully returned all categories!',
        ])
        ->assertJsonFragment([
            'name' => 'A unique name'
        ])
        ->assertJsonFragment([
            'name' => 'Unique subcategory',
        ])
        ->assertJsonMissing([
            'name' => 'Another unique name',
        ])
        ->assertJsonMissing([
            'name' => 'Another unique subcategory',
        ]);
});

test('test user cannot see another users categories', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Category::factory()->create(['user_id' => $user2->id, 'name' => 'User2 Category']);

    $response = $this->actingAs($user1)->getJson('/api/category/user-categories');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'categories')
        ->assertJsonMissing(['name' => 'User2 Category']);
});

//! Test store method
test('test if user can create a new parent or child category', function () {
    $user = User::factory()->create();

    $categoryData = [
        'user_id' => $user->id,
        'name' => 'Unique name',
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
        'parent_id' => null,
    ];

    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', $categoryData);

    $response->assertStatus(201)
        ->assertJson(['message' => 'New category created successfully!'])
        ->assertJsonFragment([
            'name' => 'Unique name',
        ])
        ->assertJsonStructure([
            "category" => [
                "id",
                "name",
                "type",
                "color",
                "icon",
                "parent_id"
            ]
        ]);
});

test('test if user can create a new category with a not unique name for their user_id', function () {
    $user = User::factory()->create();

    $categoryData = [
        'user_id' => $user->id,
        'name' => 'Unique name',
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
        'parent_id' => null,
    ];

    $response1 = $this->actingAs($user)->postJson('/api/category/user-categories/store', $categoryData);
    $response1->assertStatus(201);

    $response2 = $this->actingAs($user)->postJson('/api/category/user-categories/store', $categoryData);
    $response2->assertStatus(422)
        ->assertJson(['message' => 'The name has already been taken.']);
});

test('user can create a new category even if the name is not unique in the database but it is unique to the user_id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $categoryData1 = [
        'user_id' => $user1->id,
        'name' => 'Unique name',
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
        'parent_id' => null,
    ];

    $categoryData2 = [
        'user_id' => $user2->id,
        'name' => 'Unique name',
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
        'parent_id' => null,
    ];

    $response1 = $this->actingAs($user1)->postJson('/api/category/user-categories/store', $categoryData1);
    $response1->assertStatus(201);

    $response2 = $this->actingAs($user2)->postJson('/api/category/user-categories/store', $categoryData2);
    $response2->assertStatus(201);
});

test('user tries to use a parent id for creating a new child category which does not belong to them', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user1->id,
    ]);

    $categoryData2 = [
        'user_id' => $user2->id,
        'name' => 'Unique name',
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
        'parent_id' => $category->id,
    ];

    $response2 = $this->actingAs($user2)->postJson('/api/category/user-categories/store', $categoryData2);
    $response2->assertStatus(422)
    ->assertJson(['message' => 'The selected parent id is invalid.']);
});

test('test category validation error for name when storing', function () {
    $user = User::factory()->create();

    // Test missing name
    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', [
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    // Test name too short
    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', [
        'name' => 'ab', // Less than 3 characters
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('test category validation error for type when storing', function () {
    $user = User::factory()->create();

    // Test missing type
    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', [
        'name' => 'Test Category',
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);

    // Test invalid type
    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', [
        'name' => 'Test Category',
        'type' => 'invalid_type',
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

test('test category validation error for color when storing', function () {
    $user = User::factory()->create();

    // Test invalid color format
    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', [
        'name' => 'Test Category',
        'type' => CategoryType::Income->value,
        'color' => 'invalid_color',
        'icon' => 'shopping-cart',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);

    // Test color too short
    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', [
        'name' => 'Test Category',
        'type' => CategoryType::Income->value,
        'color' => '#fff', // Less than 7 characters
        'icon' => 'shopping-cart',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);
});

test('test category validation error for icon when storing', function () {
    $user = User::factory()->create();

    // Test icon too long
    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', [
        'name' => 'Test Category',
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => str_repeat('a', 51),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['icon']);
});

test('test category validation error for parent_id when storing', function () {
    $user = User::factory()->create();

    // Test invalid parent_id (non-existent)
    $response = $this->actingAs($user)->postJson('/api/category/user-categories/store', [
        'name' => 'Test Category',
        'type' => CategoryType::Income->value,
        'color' => '#4ecdc4',
        'icon' => 'shopping-cart',
        'parent_id' => 99999, // Non-existent ID
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['parent_id']);
});

//! Test show method
test('test user can see his own category', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id
    ]);

    $response = $this->actingAs($user)->getJson("/api/category/user-categories/{$category->id}");

    $response->assertStatus(200)
    ->assertJsonStructure([
            "category" => [
                "id",
                "name",
                "type",
                "color",
                "icon",
                "parent_id"
            ]
        ]);
});

test('test user cannot see another users category', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user1->id
    ]);

    $response = $this->actingAs($user2)->getJson("/api/category/user-categories/{$category->id}");

    $response->assertStatus(403)
        ->assertJson(['message' => 'You are unauthorized to perform this action!']);
});

//! Test update method
test('user can successfully update their own category', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Category',
        'type' => CategoryType::Expense,
        'color' => '#ff0000',
        'icon' => 'test-icon'
    ]);

    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'name' => 'Updated Category Name',
        'color' => '#00ff00'
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Category updated successfully!'])
        ->assertJsonStructure([
            'category' => [
                'id',
                'name',
                'type',
                'color',
                'icon'
            ]
        ]);

    $category->refresh();
    expect($category->name)->toBe('Updated Category Name');
    expect($category->color)->toBe('#00ff00');
    expect($category->user_id)->toBe($user->id);
});

test('user can update their category without changing the name so the name proceeds to be unique for their user_id', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Category',
        'type' => CategoryType::Expense,
        'color' => '#ff0000',
        'icon' => 'test-icon'
    ]);

    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'name' => 'Test Category',
        'type' => CategoryType::Expense,
        'color' => '#ff0000',
        'icon' => 'updated-test-icon'
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Category updated successfully!'])
        ->assertJsonStructure([
            'category' => [
                'id',
                'name',
                'type',
                'color',
                'icon'
            ]
        ]);

    $category->refresh();
    expect($category->name)->toBe('Test Category');
    expect($category->color)->toBe('#ff0000');
    expect($category->user_id)->toBe($user->id);
});

test('user can move their category by changing the parent_id if the category parent_id is not NULL and does not have any children', function () {
    $user = User::factory()->create();

    $category1 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'first category',
    ]);

    $category2 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'second category',
    ]);

    $childCategory = Category::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $category1->id,
    ]);

    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$childCategory->id}", [
        'parent_id' => $category2->id,
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Category updated successfully!'])
        ->assertJsonStructure([
            'category' => [
                'id',
                'name',
                'type',
                'color',
                'icon'
            ]
        ]);

    $category2->refresh();
    $childCategory->refresh();
    expect($childCategory->parent_id)->toBe($category2->id);
});

test('user is able to update the parent_id of a parent category with parent_id null which does not have any children', function () {
    $user = User::factory()->create();

    $category1 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'first category',
    ]);

    $category2 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'second category',
    ]);

    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category2->id}", [
        'parent_id' => $category1->id,
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Category updated successfully!'])
        ->assertJsonStructure([
            'category' => [
                'id',
                'name',
                'type',
                'color',
                'icon'
            ]
        ]);

    $category2->refresh();
    expect($category2->parent_id)->toBe($category1->id);
});

test('user is not able to update a category with parent_id null which does have children', function () {
    $user = User::factory()->create();

    $category1 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'first category',
    ]);

    $category2 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'second category',
    ]);

    $childCategory = Category::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $category1->id,
    ]);

    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category1->id}", [
        'parent_id' => $category2->id,
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Cannot move a parent category!']);

    expect($category1->parent_id)->toBeNull();
});

test('user is not able to update a category with parent_id to its own id', function () {
    $user = User::factory()->create();

    $category1 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'first category',
    ]);

    $childCategory = Category::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $category1->id,
    ]);

    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$childCategory->id}", [
        'parent_id' => $childCategory->id,
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'A category cannot be a child and a parent!']);

    expect($childCategory->parent_id)->toBe($category1->id);
});

test('user is not able to update a category without parent_id to its own id', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'first category',
    ]);

    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'parent_id' => $category->id,
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'A category cannot be a child and a parent!']);

    expect($category->parent_id)->toBeNull();
});

test('test category validation error for name when updating', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category1 = Category::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Original Category',
    ]);

    $category2 = Category::factory()->create([
        'user_id' => $user1->id,
        'name' => 'Existing Category',
    ]);

    // Test name too short
    $response = $this->actingAs($user1)->putJson("/api/category/user-categories/update/{$category1->id}", [
        'name' => 'ab', // Less than 3 characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    // Test duplicate name for same user
    $response = $this->actingAs($user1)->putJson("/api/category/user-categories/update/{$category1->id}", [
        'name' => 'Existing Category', // Already exists for this user
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    // Test that same name from different user is allowed
    $category3 = Category::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Different User Category',
    ]);

    $response = $this->actingAs($user1)->putJson("/api/category/user-categories/update/{$category1->id}", [
        'name' => 'Different User Category', // Same name but different user
    ]);

    $response->assertStatus(201); // Should succeed
});

test('test category validation error for type when updating', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Category',
        'type' => CategoryType::Income,
    ]);

    // Test invalid enum type
    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'type' => 'invalid_type',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);

    // Test non-string type
    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'type' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['type']);
});

test('test category validation error for color when updating', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Category',
        'color' => '#ff0000',
    ]);

    // Test invalid color format
    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'color' => 'invalid_color',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);

    // Test color too short
    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'color' => '#fff', // Less than 7 characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);

    // Test color too long
    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'color' => '#ff00001', // More than 7 characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['color']);
});

test('test category validation error for icon when updating', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Category',
        'icon' => 'test-icon',
    ]);

    // Test icon too long
    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'icon' => str_repeat('a', 51), // More than 50 characters
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['icon']);

    // Test non-string icon
    $response = $this->actingAs($user)->putJson("/api/category/user-categories/update/{$category->id}", [
        'icon' => 123,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['icon']);
});

test('test category validation error for parent_id when updating', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category1 = Category::factory()->create([
        'user_id' => $user1->id,
    ]);

    $category2 = Category::factory()->create([
        'user_id' => $user2->id, // Different user
    ]);

    // Test invalid parent_id (non-existent)
    $response = $this->actingAs($user1)->putJson("/api/category/user-categories/update/{$category1->id}", [
        'parent_id' => 99999, // Non-existent ID
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['parent_id']);

    // Test parent_id from different user
    $response = $this->actingAs($user1)->putJson("/api/category/user-categories/update/{$category1->id}", [
        'parent_id' => $category2->id, // Different user's category
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['parent_id']);

    // Test non-integer parent_id
    $response = $this->actingAs($user1)->putJson("/api/category/user-categories/update/{$category1->id}", [
        'parent_id' => 'invalid_id',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['parent_id']);
});

//! Test delete method
test('test if user can delete their own child or parent categories', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->deleteJson("/api/category/user-categories/delete/{$category->id}");

    $response->assertStatus(200)
    ->assertJson(['message' => 'Successfully deleted a category!']);
});

test('test if a user can delete another users child or parent categories', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user1->id,
    ]);
    $childCategory = Category::factory()->create([
        'user_id' => $user1->id,
        'parent_id' => $category->id,
    ]);

    $responseParent = $this->actingAs($user2)->deleteJson("/api/category/user-categories/delete/{$category->id}");
    $responseChild = $this->actingAs($user2)->deleteJson("/api/category/user-categories/delete/{$childCategory->id}");

    $responseParent->assertStatus(403)
        ->assertJson(['message' => 'You are unauthorized to perform this action!']);
    
    $responseChild->assertStatus(403)
        ->assertJson(['message' => 'You are unauthorized to perform this action!']);

});

test('test if after deleting parent category all the child categories are deleted as well', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
    ]);
    $childCategory = Category::factory()->create([
        'user_id' => $user->id,
        'parent_id' => $category->id,
    ]);

    $response = $this->actingAs($user)->deleteJson("/api/category/user-categories/delete/{$category->id}");

    $response->assertStatus(200)
    ->assertJson(['message' => 'Successfully deleted a category!']);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    $this->assertDatabaseMissing('categories', ['id' => $childCategory->id]);
});

test('test if user tries to delete a non-existent category', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/api/category/user-categories/delete/1");

    $response->assertStatus(404);
});

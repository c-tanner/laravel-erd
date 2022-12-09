<?php

namespace Recca0120\LaravelErdGo\Tests;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Recca0120\LaravelErdGo\Relation;
use Recca0120\LaravelErdGo\RelationFinder;
use Recca0120\LaravelErdGo\Tests\fixtures\Models\Car;
use Recca0120\LaravelErdGo\Tests\fixtures\Models\Comment;
use Recca0120\LaravelErdGo\Tests\fixtures\Models\Mechanic;
use Recca0120\LaravelErdGo\Tests\fixtures\Models\Owner;
use Recca0120\LaravelErdGo\Tests\fixtures\Models\Post;
use Recca0120\LaravelErdGo\Tests\fixtures\Models\User;
use ReflectionException;
use Spatie\Permission\Models\Role;

class RelationFinderTest extends TestCase
{
    use RefreshDatabase;

    private RelationFinder $finder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->finder = new RelationFinder();
    }

    /**
     * @throws ReflectionException
     */
    public function test_find_mechanic_relations(): void
    {
        $relations = $this->givenRelations(Mechanic::class);

        /** @var Relation $car */
        $car = $relations->get('car');
        self::assertEquals(HasOne::class, $car->type());
        self::assertEquals(Car::class, $car->related());
        self::assertEquals('mechanics.id', $car->localKey());
        self::assertEquals('cars.mechanic_id', $car->foreignKey());
    }

    /**
     * @throws ReflectionException
     */
    public function test_find_car_relations(): void
    {
        $relations = $this->givenRelations(Car::class);

        /** @var Relation $mechanic */
        $mechanic = $relations->get('mechanic');
        self::assertEquals(BelongsTo::class, $mechanic->type());
        self::assertEquals(Mechanic::class, $mechanic->related());
        self::assertEquals('cars.mechanic_id', $mechanic->localKey());
        self::assertEquals('mechanics.id', $mechanic->foreignKey());

        /** @var Relation $owner */
        $owner = $relations->get('owner');
        self::assertEquals(HasOne::class, $owner->type());
        self::assertEquals(Owner::class, $owner->related());
        self::assertEquals('cars.id', $owner->localKey());
        self::assertEquals('owners.car_id', $owner->foreignKey());
    }

    /**
     * @throws ReflectionException
     */
    public function test_find_owner_relations(): void
    {
        $relations = $this->givenRelations(Owner::class);

        /** @var Relation $car */
        $car = $relations->get('car');
        self::assertEquals(BelongsTo::class, $car->type());
        self::assertEquals(Car::class, $car->related());
        self::assertEquals('owners.car_id', $car->localKey());
        self::assertEquals('cars.id', $car->foreignKey());
    }

    /**
     * @throws ReflectionException
     */
    public function test_find_post_relations(): void
    {
        $relations = $this->givenRelations(Post::class);

        /** @var Relation $comments */
        $comments = $relations->get('comments');
        self::assertEquals(HasMany::class, $comments->type());
        self::assertEquals(Comment::class, $comments->related());
        self::assertEquals('posts.id', $comments->localKey());
        self::assertEquals('comments.post_id', $comments->foreignKey());

        /** @var Relation $user */
        $user = $relations->get('user');
        self::assertEquals(BelongsTo::class, $user->type());
        self::assertEquals(User::class, $user->related());
        self::assertEquals('posts.user_id', $user->localKey());
        self::assertEquals('users.id', $user->foreignKey());
    }

    /**
     * @throws ReflectionException
     */
    public function test_find_user_relations(): void
    {
        $relations = $this->givenRelations(User::class);

        /** @var Relation $latestPost */
        $latestPost = $relations->get('latestPost');
        self::assertEquals(HasOne::class, $latestPost->type());
        self::assertEquals(Post::class, $latestPost->related());
        self::assertEquals('users.id', $latestPost->localKey());
        self::assertEquals('posts.user_id', $latestPost->foreignKey());

        /** @var Relation $oldestPost */
        $oldestPost = $relations->get('oldestPost');
        self::assertEquals(HasOne::class, $oldestPost->type());
        self::assertEquals(Post::class, $oldestPost->related());
        self::assertEquals('users.id', $oldestPost->localKey());
        self::assertEquals('posts.user_id', $oldestPost->foreignKey());
    }

    /**
     * @throws ReflectionException
     */
    public function test_find_user_roles_relations(): void
    {
        $relations = $this->givenRelations(User::class);

        /** @var Relation $roles */
        $roles = $relations->get('roles');
        self::assertEquals(MorphToMany::class, $roles->type());
        self::assertEquals(Role::class, $roles->related());
        self::assertEquals('users.id', $roles->localKey());
        self::assertEquals('model_has_roles.model_id', $roles->foreignKey());
        self::assertEquals('model_has_roles', $roles->pivot()->table());
        self::assertEquals('model_has_roles.role_id', $roles->pivot()->localKey());
        self::assertEquals('roles.id', $roles->pivot()->foreignKey());
        self::assertEquals('model_type', $roles->pivot()->morphType());
        self::assertEquals(User::class, $roles->pivot()->morphClass());
    }

    /**
     * @throws ReflectionException
     */
    private function givenRelations(string $model): Collection
    {
        return $this->finder->generate($model);
    }
}
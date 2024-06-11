<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Document
 *
 * @property int $id
 * @property int $project_id
 * @property int|null $parent_id
 * @property string $title
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Staudenmeir\LaravelAdjacencyList\Eloquent\Collection<int, Document> $children
 * @property-read int|null $children_count
 * @property-read Document|null $parent
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Collection<int, static> all($columns = ['*'])
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document breadthFirst()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document depthFirst()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Collection<int, static> get($columns = ['*'])
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document getExpressionGrammar()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document hasChildren()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document hasParent()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document isLeaf()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document isRoot()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document newModelQuery()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document newQuery()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document query()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document tree($maxDepth = null)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document treeOf(\Illuminate\Database\Eloquent\Model|callable $constraint, $maxDepth = null)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document whereContent($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document whereCreatedAt($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document whereDepth($operator, $value = null)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document whereId($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document whereParentId($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document whereProjectId($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document whereTitle($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document whereUpdatedAt($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document withGlobalScopes(array $scopes)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Document withRelationshipExpression($direction, callable $constraint, $initialDepth, $from = null, $maxDepth = null)
 * @mixin \Eloquent
 */
	class IdeHelperDocument {}
}

namespace App{
/**
 * App\Project
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Staudenmeir\LaravelAdjacencyList\Eloquent\Collection<int, \App\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Repository> $repositories
 * @property-read int|null $repositories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\TestPlan> $testPlans
 * @property-read int|null $test_plans_count
 * @method static \Illuminate\Database\Eloquent\Builder|Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Project whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperProject {}
}

namespace App{
/**
 * App\Repository
 *
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property string $prefix
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Staudenmeir\LaravelAdjacencyList\Eloquent\Collection<int, \App\Suite> $suites
 * @property-read int|null $suites_count
 * @method static \Illuminate\Database\Eloquent\Builder|Repository newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Repository newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Repository query()
 * @method static \Illuminate\Database\Eloquent\Builder|Repository whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Repository whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Repository whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Repository wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Repository whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Repository whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Repository whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperRepository {}
}

namespace App{
/**
 * App\Suite
 *
 * @property int $id
 * @property int $repository_id
 * @property int|null $parent_id
 * @property string $title
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Staudenmeir\LaravelAdjacencyList\Eloquent\Collection<int, Suite> $children
 * @property-read int|null $children_count
 * @property-read Suite|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\TestCase> $testCases
 * @property-read int|null $test_cases_count
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Collection<int, static> all($columns = ['*'])
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite breadthFirst()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite depthFirst()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Collection<int, static> get($columns = ['*'])
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite getExpressionGrammar()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite hasChildren()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite hasParent()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite isLeaf()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite isRoot()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite newModelQuery()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite newQuery()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite query()
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite tree($maxDepth = null)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite treeOf(\Illuminate\Database\Eloquent\Model|callable $constraint, $maxDepth = null)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite whereCreatedAt($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite whereDepth($operator, $value = null)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite whereId($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite whereOrder($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite whereParentId($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite whereRepositoryId($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite whereTitle($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite whereUpdatedAt($value)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite withGlobalScopes(array $scopes)
 * @method static \Staudenmeir\LaravelAdjacencyList\Eloquent\Builder|Suite withRelationshipExpression($direction, callable $constraint, $initialDepth, $from = null, $maxDepth = null)
 * @mixin \Eloquent
 */
	class IdeHelperSuite {}
}

namespace App{
/**
 * App\TestCase
 *
 * @property int $id
 * @property int $suite_id
 * @property string $title
 * @property int $automated
 * @property int $priority
 * @property string|null $data
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase whereAutomated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase whereSuiteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCase whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperTestCase {}
}

namespace App{
/**
 * App\TestPlan
 *
 * @property int $id
 * @property int $project_id
 * @property int $repository_id
 * @property string $title
 * @property string|null $description
 * @property string|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan whereRepositoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestPlan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperTestPlan {}
}

namespace App{
/**
 * App\TestRun
 *
 * @property int $id
 * @property int $test_plan_id
 * @property int $project_id
 * @property string $title
 * @property string|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun whereTestPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestRun whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperTestRun {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class IdeHelperUser {}
}


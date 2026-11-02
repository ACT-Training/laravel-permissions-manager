<?php

namespace ACTTraining\PermissionsManager\Tests\Unit;

use ACTTraining\PermissionsManager\Enums\PermissionCategoryEnum;
use ACTTraining\PermissionsManager\Support\PermissionCategories;
use ACTTraining\PermissionsManager\Tests\Fixtures\LegacySpatieCategoryEnum;
use ACTTraining\PermissionsManager\Tests\Fixtures\UnlabelledCategoryEnum;
use ACTTraining\PermissionsManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PermissionCategoriesTest extends TestCase
{
    #[Test]
    public function it_reads_the_default_native_enum(): void
    {
        config()->set('permissions-manager.category_enum', PermissionCategoryEnum::class);

        $this->assertSame([
            'admin' => 'Admin',
            'users' => 'Users',
            'content' => 'Content',
            'settings' => 'Settings',
            'other' => 'Other',
        ], PermissionCategories::options());
        $this->assertSame('Admin', PermissionCategories::label('admin'));
        $this->assertSame('pink', PermissionCategories::color('admin'));
    }

    #[Test]
    public function it_labels_native_enum_cases_without_a_label_method_from_their_name(): void
    {
        config()->set('permissions-manager.category_enum', UnlabelledCategoryEnum::class);

        $this->assertSame(['data_kpis' => 'Data Kpis'], PermissionCategories::options());
        $this->assertSame('green', PermissionCategories::color('data_kpis'));
    }

    #[Test]
    public function it_still_reads_spatie_enum_categories(): void
    {
        config()->set('permissions-manager.category_enum', LegacySpatieCategoryEnum::class);

        $this->assertSame([
            'meetings' => 'Meetings',
            'data_kpis' => 'Data & KPIs',
        ], PermissionCategories::options());
        $this->assertSame('Data & KPIs', PermissionCategories::label('data_kpis'));
        $this->assertSame('blue', PermissionCategories::color('meetings'));
    }

    #[Test]
    public function it_returns_null_for_unknown_or_empty_categories(): void
    {
        $this->assertNull(PermissionCategories::label('not-a-category'));
        $this->assertNull(PermissionCategories::color('not-a-category'));
        $this->assertNull(PermissionCategories::label(null));
    }
}

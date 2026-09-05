<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Support\UrlHelper;
use Tests\TestCase;

class UrlHelperTest extends TestCase
{
    private function helper(): UrlHelper
    {
        return app(UrlHelper::class);
    }

    public function test_normalize_trims_whitespace_and_slashes(): void
    {
        $this->assertSame('foo/bar', $this->helper()->normalize('  /foo/bar/  '));
    }

    public function test_normalize_handles_null(): void
    {
        $this->assertSame('', $this->helper()->normalize(null));
    }

    public function test_normalize_for_link_returns_root_for_empty(): void
    {
        $this->assertSame('/', $this->helper()->normalizeForLink(''));
    }

    public function test_normalize_for_link_prefixes_slash(): void
    {
        $this->assertSame('/foo', $this->helper()->normalizeForLink('foo'));
    }

    public function test_slug_supports_nested_segments(): void
    {
        $this->assertSame('parent/child-page', $this->helper()->slug('Parent/Child Page'));
    }
}

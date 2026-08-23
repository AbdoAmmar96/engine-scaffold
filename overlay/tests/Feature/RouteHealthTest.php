<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Tests\TestCase;

/**
 * صحّة جدول الراوتات.
 *
 * النوعين دول اتكسروا فعلًا في المشروع ده:
 *
 * 1. 45 راوت `api/v1/*` فضلوا شهور ورا `auth:sanctum` والحزمة مش مركّبة —
 *    كل نداء كان بيرمي استثناء، ومحدش حس لأن مفيش حاجة بتنده الراوتات دي.
 * 2. راوت فضل بيشاور على كنترولر اتمسح، فـ `route:list` نفسه بقى بيرمي
 *    ReflectionException — يعني الأمر اللي بتشخّص بيه بيقع هو كمان.
 *
 * الاتنين مبيبانوش في تصفّح عادي: الصفحة اللي محدش بيفتحها مبتشتكيش.
 * الاختبارات دي بتفتح كل راوت مرة واحدة على مستوى التعريف مش على مستوى النداء.
 */
class RouteHealthTest extends TestCase
{
    public function test_no_route_hides_behind_an_undefined_auth_guard(): void
    {
        $guards = array_keys(config('auth.guards'));
        $broken = [];

        foreach (Router::getRoutes() as $route) {
            foreach ($this->middlewareOf($route) as $middleware) {
                if (! str_starts_with($middleware, 'auth:')) {
                    continue;
                }

                // `auth:web,api` بيقبل أكتر من جارد مفصولين بفاصلة
                foreach (explode(',', substr($middleware, 5)) as $guard) {
                    if (! in_array($guard, $guards, true)) {
                        $broken[] = $route->uri().' → auth:'.$guard;
                    }
                }
            }
        }

        $this->assertSame([], $broken, "راوتات ورا جارد مش معرّف:\n".implode("\n", $broken));
    }

    public function test_every_route_points_at_an_action_that_exists(): void
    {
        $broken = [];

        foreach (Router::getRoutes() as $route) {
            $action = $route->getActionName();

            // الكلوجرات مالهاش كلاس تتأكد منه
            if ($action === 'Closure' || ! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action, 2);

            if (! class_exists($class)) {
                $broken[] = $route->uri().' → الكلاس '.$class.' مش موجود';

                continue;
            }

            if (! method_exists($class, $method)) {
                $broken[] = $route->uri().' → '.$class.' مفيهوش '.$method.'()';
            }
        }

        $this->assertSame([], $broken, "راوتات بتشاور على حاجة مش موجودة:\n".implode("\n", $broken));
    }

    public function test_no_route_requires_email_verification(): void
    {
        // تفعيل الإيميل مش مركّب — أي راوت ورا `verified` مقفول على الكل
        // من غير ما يقول كده. لو اتركّب يومًا، شيل الاختبار ده.
        $broken = [];

        foreach (Router::getRoutes() as $route) {
            if (in_array('verified', $this->middlewareOf($route), true)) {
                $broken[] = $route->uri();
            }
        }

        $this->assertSame([], $broken, "راوتات ورا verified وتفعيل الإيميل مش مركّب:\n".implode("\n", $broken));
    }

    /** @return list<string> */
    private function middlewareOf(Route $route): array
    {
        // gatherMiddleware بيرجّع ميدل وير المجموعة كمان، ومعاها كلوجرات أحيانًا
        return array_values(array_filter($route->gatherMiddleware(), 'is_string'));
    }
}

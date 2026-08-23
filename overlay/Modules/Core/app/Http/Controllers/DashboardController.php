<?php

namespace Modules\Core\Http\Controllers;

use App\Models\User;
use App\Support\Scheduler;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Compounds\Models\Compound;
use Modules\Core\Database\Seeders\RolePermissionSeeder;
use Modules\Leads\Models\Lead;
use Modules\Properties\Models\Property;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $all = $user->seesEverything();

        // نفس قاعدة العزل بتاعة الشاشات: الأدمن بيعدّ الكل، وغيره بيعدّ بتاعه
        $own = fn ($query) => $all ? $query : $query->where('owner_id', $user->id);

        $role = $user->roles->pluck('name')->first();

        return Inertia::render('Admin/Dashboard', [
            // الجدولة تخصّ مين بيقدر يظبط السيرفر — الوسيط مالوش دعوة بيها،
            // وتحذير محدش يقدر يتصرّف فيه بيتحوّل لضوضاء بتتجاهل
            'scheduler' => $user->can('manage settings') ? Scheduler::status() : null,
            'role' => [
                'key' => $role,
                'label' => RolePermissionSeeder::ROLES[$role]['label'] ?? '—',
                'note' => RolePermissionSeeder::ROLES[$role]['note'] ?? '',
                'scoped' => ! $all,
            ],
            'stats' => array_values(array_filter([
                $user->can('manage catalog') || $user->can('manage listings') ? [
                    'label' => $all ? 'عقار' : 'وحداتك',
                    'value' => $own(Property::query())->count(),
                    'href' => '/admin/properties',
                ] : null,
                $user->can('manage catalog') || $user->can('manage projects') ? [
                    'label' => $all ? 'كمبوند' : 'مشاريعك',
                    'value' => $own(Compound::query())->count(),
                    'href' => '/admin/compounds',
                ] : null,
                $user->can('manage leads') ? [
                    'label' => 'طلب جديد',
                    'value' => $own(Lead::query())->where('status', 'new')->count(),
                    'href' => '/admin/leads',
                ] : null,
                $user->can('manage users') ? [
                    'label' => 'مستخدم',
                    'value' => User::count(),
                    'href' => '/admin/users',
                ] : null,
            ])),
        ]);
    }
}

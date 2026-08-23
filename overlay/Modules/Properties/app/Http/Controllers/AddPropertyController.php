<?php

namespace Modules\Properties\Http\Controllers;

use App\Models\User;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Leads\Models\Lead;
use Modules\Properties\Models\Property;
use Modules\Properties\Notifications\ListingReceivedNotification;
use Modules\Properties\Support\HandlesListingInput;

/**
 * «أضف عقارك» — الفورم العام.
 *
 * الطلب بيتحوّل لحاجتين مع بعض: وحدة في انتظار المراجعة (عشان تدخل
 * دورة الاعتماد زي أي وحدة تانية بدل ما تتكتب تاني بالإيد)، وطلب في
 * صندوق الطلبات (عشان حد يكلّم صاحبها). الاتنين مربوطين ببعض.
 *
 * **الزائر بياخد حساب.** قبل كده الوحدة كانت بتتسجّل بـ `owner_id = null`،
 * والصفحة بتقوله «تابع وحداتك من وحداتي» وهو مالوش حساب — وحتى لو سجّل،
 * الشاشة بتفلتر على المالك فمكانش هيشوفها أبدًا. دلوقتي الإيميل بيحدّد
 * الحساب: موجود بيتربط بيه، مش موجود بيتعمل واحد ويوصله لينك يحدّد كلمة سره.
 *
 * الوحدة مبتظهرش على الموقع قبل ما الأدمن يعتمدها — status = pending.
 */
class AddPropertyController extends Controller
{
    use HandlesListingInput;

    public function create(string $locale): Response
    {
        $en = $locale === 'en';

        return Inertia::render('Site/AddProperty', [
            'options' => $this->listingOptions($locale),
            'meta' => Seo::page(
                $locale,
                $en ? 'Add your property' : 'أضف عقارك',
                $en
                    ? 'List your unit for free. We review the details, then publish it with a reference number and get you buyer requests.'
                    : 'اعرض وحدتك مجانًا. بنراجع البيانات، وبعدها بتتنشر بكود مرجعي وبتوصلك طلبات المشترين.',
                null,
                'website',
                [Seo::breadcrumb($locale, [($en ? 'Add your property' : 'أضف عقارك') => '/add-property'])],
            ),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $en = $locale === 'en';

        // نفس مصيدة البوتس بتاعة فورم اتصل بنا — نجاح شكلي من غير ما نكتب حاجة
        if (filled($request->input('website'))) {
            return back()->with('success', $en ? 'Received ✅' : 'وصلنا طلبك ✅');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            // مطلوب: هو ده اللي بيحدّد الحساب اللي الوحدة هتتسجّل عليه
            'email' => ['required', 'email', 'max:190'],
            ...$this->listingRules(),
        ], [], [
            'name' => 'الاسم',
            'phone' => 'الموبايل',
            'email' => 'الإيميل',
            ...$this->listingAttributes(),
        ]);

        $images = $this->resolveImages(null, $data, $request->file('images') ?? []);

        $signedIn = $request->user();
        [$user, $isNewAccount] = $signedIn
            ? [$signedIn, false]
            : $this->accountFor($data);

        // العميل اللي عرض وحدة بقى «معلن»: من غير كده الوحدة بتتسجّل
        // باسمه ومايقدرش يتابعها ولا يعدّلها من حسابه.
        if ($user->hasRole('customer')) {
            $user->syncRoles(['lister']);
        }

        // بيتقرا بعد الترقية: الحساب اللي اترقّى دلوقتي بقى بيملك وحداته
        $owner = $user->ownsListings() ? $user->id : null;

        $property = Property::create($this->listingColumns($data, $images) + [
            'owner_id' => $owner,
            // القيمة صريحة: ديفولت الموديل «منشور»، وده مسار عام
            'status' => 'pending',
            'is_active' => true,
        ]);

        Lead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'message' => $this->summary($data, $property),
            'source' => 'listing',
            'status' => 'new',
            'property_id' => $property->id,
            'owner_id' => $owner,
            'user_id' => $user->ownsListings() ? null : $user->id,
        ]);

        // المسجّل شايف وحدته في «وحداتي» على طول — مش محتاج رسالة
        if (! $signedIn) {
            $user->notify(new ListingReceivedNotification($property, $locale, $isNewAccount));
        }

        return back()->with('success', $en
            ? 'Your property was received ✅ — our team reviews it and publishes it within 24 hours. Check your email to follow it from your account.'
            : 'وصلنا عقارك ✅ — الفريق بيراجعه وبينشره خلال ٢٤ ساعة. بص في بريدك عشان تتابعه من حسابك.');
    }

    /**
     * الحساب اللي الوحدة هتتسجّل عليه، والإيميل هو المفتاح.
     *
     * **مبنقولش للي بعت إن الإيميل عليه حساب ولا لأ** — الرسالة اللي بترجع
     * واحدة في الحالتين، زي «نسيت كلمة المرور» بالظبط. غير كده الفورم بيبقى
     * أداة يعرف بيها أي حد مين عنده حساب هنا.
     *
     * وكلمة السر عشوائية مش فاضية: الحساب مايتفتحش غير باللينك اللي في
     * الإيميل، فحتى لو حد بعت بإيميل مش بتاعه مش هياخد وصول لحاجة —
     * والوحدة نفسها `pending` ومش بتظهر قبل مراجعة الأدمن.
     *
     * @param  array<string, mixed>  $data
     * @return array{0: User, 1: bool}
     */
    private function accountFor(array $data): array
    {
        $existing = User::where('email', $data['email'])->first();

        if ($existing) {
            return [$existing, false];
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Str::password(32),
        ]);

        $user->syncRoles(['lister']);

        return [$user, true];
    }

    /** ملخّص الطلب في صندوق الطلبات — الأدمن يقرا الأساسي من غير ما يفتح الوحدة */
    private function summary(array $data, Property $property): string
    {
        $lines = ['عرض عقار من الموقع: '.$data['title']];

        if ($price = Property::money($data['price_amount'] ?? null)) {
            $lines[] = 'السعر المطلوب: '.$price;
        }

        if ($data['description'] ?? null) {
            $lines[] = $data['description'];
        }

        $lines[] = 'المراجعة: /admin/properties/'.$property->id.'/edit';

        return implode("\n", $lines);
    }
}

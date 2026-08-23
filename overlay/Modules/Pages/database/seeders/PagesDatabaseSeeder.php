<?php

namespace Modules\Pages\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Pages\Models\Page;

/**
 * صفحتين الموقع مش بيشتغل قانونيًا من غيرهم: سياسة الخصوصية والشروط.
 *
 * **بتتزرع مسوّدات عن قصد.** النص اللي تحت مسوّدة شغل جاهزة مش وثيقة نهائية:
 * فيها مواضع لازم تتملا (اسم الشركة، السجل التجاري، مدة الاحتفاظ بالبيانات)
 * ومحتاجة مراجعة قانونية. صفحة خصوصية منشورة وبتوعد بحاجة الموقع مش بيعملها
 * أسوأ من صفحة مش موجودة — فالنشر قرار الأدمن مش قرار السيدر.
 *
 * idempotent: بيتعرّف بالـ slug، فالتشغيل تاني مش بيدهس تعديلات الأدمن.
 */
class PagesDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;

        foreach ($this->pages() as $page) {
            $slug = $page['slug'];

            if (Page::where('slug', $slug)->exists()) {
                continue;
            }

            Page::create($page + ['is_active' => false]);
            $created++;
        }

        $total = Page::count();
        $drafts = Page::where('is_active', false)->count();

        $this->command->info("  صفحات: {$created} جديدة · {$total} إجمالي · {$drafts} مسوّدة");

        if ($drafts > 0) {
            $this->command->warn('  ⚠ المسوّدات محتاجة مراجعة قانونية وملء المواضع الفاضية قبل النشر');
        }
    }

    /** @return list<array<string, string|int|bool>> */
    private function pages(): array
    {
        return [
            [
                'slug' => 'privacy-policy',
                'title' => 'سياسة الخصوصية',
                'title_en' => 'Privacy Policy',
                'excerpt' => 'إيه البيانات اللي بناخدها منك، بنستخدمها في إيه، ومين بيشوفها.',
                'excerpt_en' => 'What data we collect, what we use it for, and who can see it.',
                'sort' => 10,
                'body' => <<<'TXT'
                    المستند ده بيشرح إزاي [اسم الشركة] بتتعامل مع البيانات اللي بتوصلها من خلال الموقع ده.

                    ## البيانات اللي بنجمعها
                    - **بيانات بتديهالنا بنفسك:** الاسم ورقم التليفون والإيميل لما تبعت طلب أو تعمل حساب أو تضيف وحدة.
                    - **بيانات بتتسجّل تلقائيًا:** الصفحات اللي بتفتحها، ووقت الزيارة، ونوع المتصفح، وعنوان الـ IP.
                    - **تفضيلاتك:** البحوث اللي بتحفظها والوحدات اللي بتضيفها للمفضلة والوحدات اللي شُفتها مؤخرًا.

                    ## بنستخدمها في إيه
                    - الرد على طلبك وترشيح وحدات تناسب ميزانيتك والمنطقة اللي بتدوّر فيها.
                    - إرسال تنبيه لما تنزل وحدة تطابق بحث حفظته — ودي بتقدر توقفها في أي وقت من صفحة «البحث المحفوظ».
                    - تحسين ترتيب النتايج ومعرفة أنهي أقسام بتتستخدم فعلًا.

                    **مبنبيعش بياناتك ولا بنأجّرها لأي طرف تالت.**

                    ## مين بيشوف بياناتك
                    - فريق العمل عندنا، وكل واحد بيشوف اللي شغله محتاجه بس.
                    - المطوّر أو صاحب الوحدة اللي طلبت تتواصل بخصوصها — بنبعتله بيانات التواصل بتاعتك عشان يرد عليك.
                    - الجهات الرسمية لو اتطلب منّا ده بأمر قانوني.

                    ## الكوكيز
                    بنستخدم كوكيز عشان نفضل فاكرينك وأنت داخل، ونفتكر لغة العرض اللي اخترتها. لو استخدمنا أدوات تحليلات، الكوكيز بتاعتها بتساعدنا نعرف الصفحات الأكتر زيارة من غير ما نعرف هويتك الشخصية. تقدر تقفل الكوكيز من إعدادات متصفحك — بس ساعتها الدخول والتفضيلات مش هيفضلوا محفوظين.

                    ## مدة الاحتفاظ
                    بنحتفظ ببيانات الطلب لمدة [حدّد المدة] من آخر تواصل بينّا. حساب المستخدم وبياناته بيفضلوا طول ما الحساب شغّال.

                    ## حقوقك
                    - تطلب نسخة من بياناتك اللي عندنا.
                    - تطلب تصحيح أي بيانات غلط.
                    - تطلب مسح حسابك وبياناته.
                    - توقف تنبيهات البحث المحفوظ في أي وقت.

                    للتواصل بخصوص أي طلب من دول: [إيميل التواصل].

                    ## تعديلات على السياسة
                    لو غيّرنا حاجة في السياسة دي، التاريخ اللي تحت الصفحة بيتحدّث. الاستمرار في استخدام الموقع بعد التعديل معناه الموافقة على النسخة الجديدة.
                    TXT,
                'body_en' => <<<'TXT'
                    This document explains how [Company Name] handles the data it receives through this website.

                    ## What we collect
                    - **Data you give us:** name, phone number and email when you send a request, create an account, or list a unit.
                    - **Data recorded automatically:** pages you open, visit time, browser type, and IP address.
                    - **Your preferences:** searches you save, units you favourite, and units you recently viewed.

                    ## What we use it for
                    - Answering your request and shortlisting units that fit your budget and area.
                    - Sending an alert when a unit matching a saved search is listed — you can stop these any time from the "Saved searches" page.
                    - Improving result ranking and understanding which sections are actually used.

                    **We do not sell or rent your data to any third party.**

                    ## Who can see your data
                    - Our team, each member seeing only what their work requires.
                    - The developer or unit owner you asked to be contacted about — we pass on your contact details so they can reply.
                    - Official authorities, if required by a lawful order.

                    ## Cookies
                    We use cookies to keep you signed in and to remember your display language. If analytics tools are in use, their cookies help us see which pages are most visited without identifying you personally. You can block cookies from your browser settings — sign-in and preferences will then not persist.

                    ## Retention
                    We keep request data for [specify period] from our last contact. Account data is kept while the account remains active.

                    ## Your rights
                    - Request a copy of the data we hold about you.
                    - Request correction of anything inaccurate.
                    - Request deletion of your account and its data.
                    - Stop saved-search alerts at any time.

                    To make any of these requests: [contact email].

                    ## Changes to this policy
                    If we change this policy, the date at the bottom of the page updates. Continuing to use the site after a change means you accept the new version.
                    TXT,
            ],
            [
                'slug' => 'terms',
                'title' => 'شروط الاستخدام',
                'title_en' => 'Terms of Use',
                'excerpt' => 'قواعد استخدام الموقع، وحدود مسؤوليتنا عن البيانات المعروضة.',
                'excerpt_en' => 'The rules for using this site, and the limits of our responsibility for what is listed.',
                'sort' => 20,
                'body' => <<<'TXT'
                    باستخدامك للموقع ده بتوافق على الشروط اللي تحت. لو مش موافق على أي بند، من فضلك متستخدمش الموقع.

                    ## طبيعة الخدمة
                    الموقع منصّة عرض ووساطة عقارية. إحنا بنعرض وحدات ومشاريع وبنوصّلك بصاحبها أو بمطوّرها — **إحنا مش طرف في أي تعاقد بيحصل بينك وبينه.**

                    ## دقة البيانات المعروضة
                    بنراجع البيانات قبل نشرها، بس الأسعار وحالة الوحدات بتتغيّر باستمرار وبعض البيانات بتوصلنا من المُعلن نفسه. يعني:
                    - السعر المعروض **مؤشّر** ولازم يتأكّد وقت التعاقد.
                    - توافر الوحدة مش مضمون لحد ما تتأكد منّا.
                    - الصور والمساحات والتشطيب بتوصف الوحدة النموذجية وممكن تختلف عن الوحدة الفعلية.

                    **راجع كل بيان بنفسك قبل ما تدفع أي مبلغ.**

                    ## حسابك
                    - أنت مسؤول عن سرية كلمة السر بتاعتك وعن أي نشاط بيحصل من حسابك.
                    - البيانات اللي بتدخلها لازم تكون صحيحة وبتخصّك.
                    - بنقدر نوقف أي حساب بيخالف الشروط دي.

                    ## لو بتضيف وحدة
                    - لازم تكون مالك الوحدة أو مفوّض من المالك بعرضها.
                    - البيانات والصور اللي بترفعها لازم تكون بتاعتك أو معاك حق استخدامها.
                    - كل وحدة بتتراجع قبل النشر، وأي تعديل بعد النشر بيرجّعها للمراجعة.
                    - بنقدر نرفض أو نوقف أي وحدة بياناتها ناقصة أو مضلّلة.

                    ## الاستخدام الممنوع
                    - نسخ محتوى الموقع أو سحبه آليًا لإعادة نشره.
                    - إرسال بيانات غير صحيحة أو انتحال صفة غيرك.
                    - أي محاولة للوصول لأجزاء من النظام مش مسموح لك بيها.

                    ## الملكية الفكرية
                    تصميم الموقع ونصوصه وشعاره ملك لـ [اسم الشركة]. صور الوحدات ملك لأصحابها أو للمطوّرين، ومعروضة بغرض التسويق.

                    ## حدود المسؤولية
                    مسؤوليتنا محصورة في تشغيل المنصّة وعرض البيانات. مش مسؤولين عن أي خسارة ناتجة عن تعاقد بينك وبين طرف تالت، ولا عن انقطاع مؤقت في الخدمة.

                    ## القانون الواجب التطبيق
                    الشروط دي بتخضع للقانون المصري، وأي نزاع بيرجع للمحاكم المختصة في [المدينة].

                    ## تعديل الشروط
                    ممكن نعدّل الشروط دي، والتاريخ اللي تحت بيوضّح آخر تحديث.
                    TXT,
                'body_en' => <<<'TXT'
                    By using this site you agree to the terms below. If you do not agree with any of them, please do not use the site.

                    ## What this service is
                    This site is a real-estate listing and brokerage platform. We list units and projects and connect you with the owner or developer — **we are not a party to any contract concluded between you and them.**

                    ## Accuracy of what is listed
                    We review data before publishing it, but prices and unit availability change constantly and some data reaches us from the advertiser. This means:
                    - A listed price is **indicative** and must be confirmed at contract time.
                    - Availability is not guaranteed until confirmed with us.
                    - Images, areas and finishing describe the typical unit and may differ from the actual one.

                    **Verify every detail yourself before paying any amount.**

                    ## Your account
                    - You are responsible for keeping your password confidential and for any activity from your account.
                    - The data you enter must be accurate and belong to you.
                    - We may suspend any account that breaches these terms.

                    ## If you list a unit
                    - You must be the owner or authorised by the owner to list it.
                    - The data and images you upload must be yours or licensed to you.
                    - Every unit is reviewed before publishing, and any edit after publishing returns it to review.
                    - We may reject or suspend any listing with incomplete or misleading data.

                    ## Prohibited use
                    - Copying or scraping site content for republication.
                    - Submitting false data or impersonating someone else.
                    - Any attempt to access parts of the system you are not permitted to.

                    ## Intellectual property
                    The site design, text and logo belong to [Company Name]. Unit images belong to their owners or developers and are shown for marketing purposes.

                    ## Limits of liability
                    Our responsibility is limited to operating the platform and displaying listings. We are not liable for losses arising from a contract between you and a third party, nor for temporary service interruption.

                    ## Governing law
                    These terms are governed by Egyptian law, and any dispute falls to the competent courts in [city].

                    ## Changes to these terms
                    We may amend these terms; the date below shows the last update.
                    TXT,
            ],
        ];
    }
}

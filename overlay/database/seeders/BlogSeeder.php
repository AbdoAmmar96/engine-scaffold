<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Blog\Models\Post;

/**
 * مقالات المدونة التجريبية — idempotent (updateOrCreate على الـ slug).
 */
class BlogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->posts() as $i => $post) {
            Post::updateOrCreate(
                ['slug' => $post['slug']],
                $post + ['sort' => $i, 'is_active' => true],
            );
        }

        $this->command?->info(sprintf('  مقالات: %d', Post::count()));
    }

    private function posts(): array
    {
        return [
            [
                'slug' => 'egypt-property-market-2026',
                'title' => 'السوق العقاري المصري 2026: الأرقام التي تصنع الفارق في قرارك',
                'title_en' => 'Egypt property market 2026: the numbers that change your decision',
                'category' => 'تحليل السوق',
                'category_en' => 'Market analysis',
                'author' => 'عمرو شلبي',
                'published_at' => '2026-07-28',
                'image' => '/images/demo/property-1.jpg',
                'excerpt' => 'ليس كل ارتفاع في السعر مكسبًا. الفرق بين الزيادة الاسمية والعائد الحقيقي هو ما يحدّد جودة الصفقة.',
                'excerpt_en' => 'Not every price rise is a gain. The gap between nominal increase and real return decides whether the deal is good.',
                'body' => <<<'AR'
تحرّك سوق العقارات في مصر خلال العامين الأخيرين بسرعة تفوق قدرة أغلب المشترين على المتابعة. تضاعفت الأسعار المعلنة في مناطق مثل العاصمة الإدارية والساحل الشمالي، لكن الرقم المعلن وحده لا يكفي لاتخاذ القرار.

## الفرق بين الزيادة الاسمية والعائد الحقيقي
إذا ارتفع سعر وحدة 30% في عام، وكان التضخم 25% في العام نفسه، فقد ربحت 5% حقيقية لا 30%. هذا الحساب بسيط، لكن أغلب العروض التسويقية تتجاهله تمامًا.

ولحساب العائد الحقيقي تحتاج إلى معرفة:

- سعر الشراء الفعلي بعد كل الرسوم والمصاريف الإدارية
- تكلفة التمويل عند الشراء بالتقسيط (الفرق بين الكاش والتقسيط يصل أحيانًا إلى 40%)
- العائد الإيجاري السنوي إن كنت تنوي التأجير
- مصاريف الصيانة السنوية التي تدفعها للمطوّر

## المناطق التي تبدو أرقامها منطقية حاليًا
ما زالت القاهرة الجديدة أكثر المناطق سيولة — أي أنك ستجد مشتريًا إن أردت البيع. أما العاصمة الإدارية ففيها فرص أسعار أفضل لكن سيولتها أقل، فالخروج من الاستثمار يستغرق وقتًا أطول.

والساحل الشمالي قصة مختلفة: العائد فيه موسمي بحت، ولا يتجاوز معدل الإشغال الحقيقي 12 أسبوعًا في السنة لأغلب الوحدات. فإن وعدك أحد بعائد سنوي مماثل للوحدات السكنية، فاطلب منه أرقام إشغال مكتوبة.

## السؤال الذي يجب طرحه قبل أي حجز
ليس «كم سعرها؟» بل «ما تاريخ التسليم المكتوب في العقد، وما غرامة التأخير؟». والمطوّر الذي يرفض كتابة غرامة تأخير واضحة في العقد يخبرك بشيء مهم عن ثقته في جدوله الزمني.
AR,
                'body_en' => <<<'EN'
Egypt's property market has moved faster over the past two years than most buyers could track. Asking prices have doubled in areas like the New Administrative Capital and the North Coast, but the headline number alone is not enough to decide on.

## Nominal increase vs real return
If a unit's price rose 30% in a year while inflation ran at 25%, you gained 5% in real terms — not 30%. The arithmetic is simple, yet most marketing decks ignore it entirely.

To work out the real return you need:

- The actual purchase price after all fees and administrative charges
- The financing cost if you are buying on instalments (the cash-to-instalment gap can reach 40%)
- The annual rental yield if you plan to let the unit
- The annual maintenance charge payable to the developer

## Where the numbers currently make sense
New Cairo still has the deepest liquidity — meaning if you want to sell, there is a buyer. The New Administrative Capital offers better entry prices but thinner liquidity, so exiting takes longer.

The North Coast is a different story: the return is purely seasonal, and real occupancy rarely exceeds 12 weeks a year for most units. If someone promises you a yield comparable to residential units, ask for written occupancy figures.

## The question to ask before any reservation
Not "what does it cost?" but "what is the delivery date written into the contract, and what is the late-delivery penalty?" A developer who refuses to put a clear penalty in the contract is telling you something important about confidence in their own schedule.
EN,
            ],
            [
                'slug' => 'cash-vs-installments',
                'title' => 'كاش أم تقسيط؟ الحساب الذي يوضّح الفرق الحقيقي',
                'title_en' => 'Cash or instalments? The maths that shows the real gap',
                'category' => 'دليل المشتري',
                'category_en' => "Buyer's guide",
                'author' => 'منة عادل',
                'published_at' => '2026-07-14',
                'image' => '/images/demo/property-4.jpg',
                'excerpt' => 'خطة تقسيط لثماني سنوات قد ترفع سعر الوحدة 40% فوق الكاش. متى تستحق هذه الزيادة؟',
                'excerpt_en' => 'An 8-year plan can push a unit 40% above its cash price. When is that premium worth paying?',
                'body' => <<<'AR'
يقارن أغلب العملاء بين الكاش والتقسيط بالقسط الشهري وحده. وهذه أسرع طريقة لدفع زيادة دون أن تشعر.

## احسب التكلفة الكلية أولًا
وحدة سعرها كاش 4,000,000 جنيه، وتصل بالتقسيط على ثماني سنوات إلى 5,600,000. الفرق 1,600,000 جنيه — أي زيادة 40% موزّعة على المدة.

والسؤال الصحيح: لو استثمرت الأربعة ملايين في شيء آخر، هل تدرّ أكثر من 1.6 مليون خلال ثماني سنوات؟ إن كان الجواب نعم، فالتقسيط منطقي. وإن كان لا، فأنت تدفع زيادة دون مقابل.

## متى يكون التقسيط هو القرار الصحيح
- عندما يتوفر لديك جزء من المبلغ فقط وتحتاج إلى دخول السوق قبل ارتفاع الأسعار
- عندما تكون الوحدة تحت الإنشاء والتسليم بعد سنتين أو ثلاث — فالقسط هنا يعمل كأنه ادخار إجباري
- عندما يوجد عائد إيجاري متوقع يغطّي جزءًا من القسط بعد التسليم

## متى يكون الكاش أفضل بوضوح
- عند وجود خصم كاش حقيقي 15% أو أكثر
- عندما تكون الوحدة جاهزة للاستلام فورًا وستؤجّرها من أول شهر
- عندما تحتاج إلى البيع خلال سنة أو سنتين — فبيع الوحدة المقسّطة أصعب بكثير

## نصيحة عملية
اطلب من المطوّر أن يكتب لك السعرين — الكاش والتقسيط — في العرض نفسه ومؤرَّخين. وأي مطوّر جاد سيفعل ذلك في دقيقتين.
AR,
                'body_en' => <<<'EN'
Most buyers compare cash to instalments by monthly payment alone. That is the fastest way to overpay without noticing.

## Work out the total cost first
A unit priced at EGP 4,000,000 cash comes to EGP 5,600,000 on an 8-year plan. The gap is EGP 1,600,000 — a 40% premium spread across the term.

The right question: if you invested that EGP 4m elsewhere, would it return more than EGP 1.6m over 8 years? If yes, the instalment plan makes sense. If no, you are paying a premium for nothing.

## When instalments are the right call
- You hold only part of the amount and need to enter the market before prices move
- The unit is under construction with delivery in two to three years — the instalment acts as forced saving
- There is an expected rental yield that covers part of the instalment after handover

## When cash clearly wins
- There is a genuine cash discount of 15% or more
- The unit is ready for immediate handover and you will let it from month one
- You may need to sell within a year or two — an instalment unit is far harder to resell

## A practical tip
Ask the developer to put both prices — cash and instalment — in the same written offer, dated. Any serious developer will do that in two minutes.
EN,
            ],
            [
                'slug' => 'new-capital-guide',
                'title' => 'العاصمة الإدارية: دليل الأحياء وما يفرّق بينها',
                'title_en' => 'The New Administrative Capital: a district-by-district guide',
                'category' => 'دليل المناطق',
                'category_en' => 'Area guide',
                'author' => 'كريم فؤاد',
                'published_at' => '2026-06-30',
                'image' => '/images/demo/area-2.jpg',
                'excerpt' => 'R7 ليست R8، والفرق بينهما ليس في السعر وحده — بل في الخدمات والتسليم والسيولة أيضًا.',
                'excerpt_en' => 'R7 is not R8, and the difference is not only price — it is services, delivery and liquidity too.',
                'body' => <<<'AR'
العاصمة الإدارية ليست منطقة واحدة، بل مجموعة أحياء لكل منها طبيعة مختلفة تمامًا. ومن يشتري دون أن يفهم الفرق يدفع سعر حيّ ويحصل على خدمات حيّ آخر.

## R7 — الأقرب إلى الحي الحكومي
أقرب حي إلى الحي الحكومي والمنطقة المركزية للأعمال. بنيته التحتية أكمل من غيره، ونسبة الوحدات المسلَّمة فيه أعلى. وسعره أعلى كذلك، لكن سيولته هي الأفضل في العاصمة.

## R8 — الأهدأ والأكثر خضرة
مساحات خضراء أكبر وكثافة أقل. يناسب السكن العائلي أكثر من الاستثمار قصير المدى. وما زالت التسليمات تتم على مراحل، فاسأل عن المرحلة تحديدًا لا عن المشروع كله.

## R3 — الأسعار الأقل
أبعد نسبيًا عن مركز المدينة، والأسعار تعكس ذلك. فإن كان أفقك الزمني خمس سنوات أو أكثر، فقد تكون هناك فرصة. أما إن كنت تحتاج إلى الخروج مبكرًا، فالسيولة هنا أضعف.

## ما يجب التأكد منه في أي حي
- حالة المرافق فعليًا لا على الورق — زُره بنفسك
- عدد الوحدات المسلَّمة في المرحلة نفسها
- المسافة إلى المحور الذي ستستخدمه يوميًا
- مصاريف الصيانة السنوية ونسبة زيادتها في العقد
AR,
                'body_en' => <<<'EN'
The New Administrative Capital is not one place — it is a set of districts with genuinely different characters. Buying without understanding the difference means paying for one district and receiving the services of another.

## R7 — closest to the government district
The nearest district to the government quarter and the central business district. Its infrastructure is the most complete and the share of delivered units is the highest. Prices are higher too, but liquidity here is the best in the Capital.

## R8 — quieter and greener
Larger green spaces and lower density. Better suited to family living than to short-term investment. Handovers are still phased, so ask about the specific phase rather than the project as a whole.

## R3 — the lowest prices
Relatively further from the city centre, and prices reflect that. With a five-year-plus horizon there may be an opportunity here. If you need an early exit, liquidity is weaker.

## What to verify in any district
- The actual state of utilities, not the brochure version — visit yourself
- The number of delivered units within the same phase
- The distance to the road corridor you will use daily
- Annual maintenance charges and the escalation clause in the contract
EN,
            ],
            [
                'slug' => 'contract-red-flags',
                'title' => '7 بنود في عقد العقار يجب أن تقرأها مرتين',
                'title_en' => '7 clauses in a property contract worth reading twice',
                'category' => 'قانوني',
                'category_en' => 'Legal',
                'author' => 'سارة منصور',
                'published_at' => '2026-06-12',
                'image' => '/images/demo/process.jpg',
                'excerpt' => 'أغلب المشكلات تنشأ من بنود قرأها العميل على عجل وهو مبتهج بالوحدة. وهذه أهمها.',
                'excerpt_en' => 'Most disputes start with clauses skimmed in the excitement of signing. These are the ones that matter.',
                'body' => <<<'AR'
العقد ليس إجراءً شكليًا — بل هو الشيء الوحيد الذي يحميك إن وقعت مشكلة بعد عامين. وهذه البنود التي نراجعها مع كل عميل قبل التوقيع.

## 1. تاريخ التسليم وغرامة التأخير
يجب أن يوجد تاريخ محدد باليوم والشهر والسنة، وغرامة تأخير مكتوبة بنسبة أو مبلغ. فعبارة «التسليم خلال 2026» ليست تاريخًا.

## 2. مواصفات التشطيب بالتفصيل
الماركات والخامات بالاسم. فعبارة «تشطيب سوبر لوكس» جملة تسويقية لا مواصفة.

## 3. نسبة التسامح في المساحة
تسمح أغلب العقود بفارق ±5% في المساحة. وتأكّد أن هذا الفارق يُحتسب بسعر المتر الأصلي لا بسعر السوق وقت التسليم.

## 4. شروط إعادة البيع
يمنع بعض المطوّرين البيع قبل سداد نسبة معينة، ويفرضون رسوم تنازل. فاعرف الرقم قبل التوقيع.

## 5. مصاريف الصيانة ونسبة زيادتها
ترتفع مصاريف الصيانة السنوية كل عام. ويجب أن تكون النسبة مكتوبة ومحدودة بسقف.

## 6. شرط الفسخ
ماذا يحدث إن تأخرت في قسط؟ هل توجد مهلة؟ هل توجد غرامة؟ ومتى يحق للمطوّر فسخ العقد، وكم يردّ إليك؟

## 7. الضمانات بعد التسليم
مدة ضمان الهيكل الإنشائي والتشطيبات والأجهزة. ويجب أن تكون مكتوبة بمدة واضحة.

## نصيحة أخيرة
خُذ نسخة من العقد قبل التوقيع بأسبوع على الأقل، وراجعها مع محامٍ. والمطوّر الذي يضغط عليك للتوقيع في اليوم نفسه إنما يوفّر عليك وقت مراجعة أنت في حاجة إليه.
AR,
                'body_en' => <<<'EN'
The contract is not a formality — it is the only thing that protects you if something goes wrong two years from now. These are the clauses we review with every client before signing.

## 1. Delivery date and late-delivery penalty
There must be a specific day, month and year, plus a written penalty as a percentage or amount. "Delivery during 2026" is not a date.

## 2. Detailed finishing specification
Brands and materials by name. "Super lux finishing" is marketing copy, not a specification.

## 3. Area tolerance
Most contracts allow a ±5% variance in area. Make sure any difference is settled at the original price per metre, not the market price at handover.

## 4. Resale conditions
Some developers bar resale before a set percentage is paid and charge an assignment fee. Learn the number before you sign.

## 5. Maintenance charges and escalation
Annual maintenance rises every year. The rate must be written down and capped.

## 6. Termination clause
What happens if you are late on an instalment? Is there a grace period? A penalty? When can the developer terminate, and how much do you get back?

## 7. Post-handover warranties
Structural warranty period, finishes, and appliances. Each needs a clearly stated duration.

## One last tip
Get a copy of the contract at least a week before signing and review it with a lawyer. A developer pressing you to sign same-day is saving you the review time you actually need.
EN,
            ],
            [
                'slug' => 'north-coast-real-yield',
                'title' => 'الساحل الشمالي: العائد الحقيقي مقابل العائد المعلن',
                'title_en' => 'The North Coast: real yield versus advertised yield',
                'category' => 'تحليل السوق',
                'category_en' => 'Market analysis',
                'author' => 'عمرو شلبي',
                'published_at' => '2026-05-22',
                'image' => '/images/demo/compound-2.jpg',
                'excerpt' => 'عروض كثيرة تتحدث عن عائد سنوي 12%. كيف يُحتسب هذا الرقم — وما الذي يُحذف منه؟',
                'excerpt_en' => 'Many offers quote a 12% annual yield. How is it calculated — and what gets left out?',
                'body' => <<<'AR'
الساحل الشمالي من أكثر الأسواق التي تُذكر فيها أرقام عائد كبيرة، وأقلّها شفافية في طريقة الحساب.

## كيف يُحتسب العائد المعلن
تحسب أغلب العروض: إيجار الأسبوع × عدد أسابيع الموسم ÷ سعر الوحدة. وهذا الحساب يفترض إشغالًا كاملًا طوال الموسم، وهو أمر نادر الحدوث.

## ما الذي يُحذف من الحساب
- عمولة الإدارة (15% إلى 20% من الإيجار)
- مصاريف الصيانة السنوية
- تكلفة التجهيز والفرش والتجديد كل ثلاث سنوات
- الفترات التي تبقى فيها الوحدة شاغرة

وبعد خصم ذلك كله، يتراوح العائد الحقيقي في أغلب الحالات بين 4% و7% لا 12%.

## متى يكون الساحل قرارًا جيدًا
إن كنت تشتري للاستخدام الشخصي وتعدّ الإيجار مساهمة في المصاريف لا استثمارًا، فالحساب مختلف تمامًا وقد يكون القرار ممتازًا.

أما إن كنت تشتري بغرض الاستثمار البحت، فقارن بالعائد الإيجاري في القاهرة الجديدة — فهو أهدأ، وعلى مدار السنة، وسيولته أعلى وقت البيع.

## السؤال الذي يوضّح كل شيء
اطلب ممن يعرض عليك: «هل يمكنك أن تعطيني أرقام إشغال فعلية للعام الماضي للمشروع نفسه؟» فالإجابة عن هذا السؤال تصنع الفارق كله.
AR,
                'body_en' => <<<'EN'
The North Coast is among the markets where the largest yield figures are quoted — and where the calculation is least transparent.

## How the advertised yield is calculated
Most offers compute: weekly rent × season weeks ÷ unit price. That assumes full occupancy across the season, which rarely happens.

## What gets left out
- Management commission (15% to 20% of rent)
- Annual maintenance charges
- Furnishing, fit-out and refurbishment every three years
- The weeks the unit sits empty

Once you deduct all of that, the real yield in most cases lands between 4% and 7% — not 12%.

## When the North Coast is a good decision
If you are buying for personal use and treat rent as a contribution to costs rather than an investment, the arithmetic changes completely and the decision can be excellent.

If you are buying purely as an investment, compare against rental yields in New Cairo — steadier, year-round, and more liquid at resale.

## The question that clarifies everything
Ask whoever is pitching you: "Can you give me actual occupancy figures for the same project last year?" The answer to that question tells you everything.
EN,
            ],
            [
                'slug' => 'first-apartment-checklist',
                'title' => 'أول شقة: قائمة مراجعة قبل دفع جدية الحجز',
                'title_en' => 'First apartment: a checklist before you pay the reservation fee',
                'category' => 'دليل المشتري',
                'category_en' => "Buyer's guide",
                'author' => 'منة عادل',
                'published_at' => '2026-05-05',
                'image' => '/images/demo/property-7.jpg',
                'excerpt' => 'جدية الحجز غير مستردة في أغلب الأحوال. تأكّد من هذه الأمور قبل دفعها.',
                'excerpt_en' => 'Reservation fees are usually non-refundable. Check these before you pay one.',
                'body' => <<<'AR'
تُدفع جدية الحجز قبل مراجعة العقد في أغلب الحالات، وهي غالبًا غير مستردة. أي أن القرار الفعلي يُتخذ في تلك اللحظة لا وقت التوقيع.

## قبل الدفع
- عاين الوحدة نفسها أو وحدة مماثلة مسلَّمة في المشروع نفسه
- اطلب صورة من رخصة البناء وشهادة الصلاحية
- تأكّد أن المطوّر سلّم مشروعًا من قبل فعلًا — لا مجرد نية تسليم
- اسأل عن نسبة الوحدات المباعة في المرحلة نفسها
- خُذ عرض السعر مكتوبًا ومؤرَّخًا وموقّعًا

## اسأل عن التمويل مبكرًا
إن كنت ستحتاج تمويلًا عقاريًا، فابدأ إجراءاته قبل الحجز لا بعده. فقد يرفض البنك الوحدة نفسها لأسباب لا علاقة لها بك.

## احسب المصاريف الجانبية
- رسوم التسجيل والشهر العقاري
- مصاريف التوصيلات (كهرباء، غاز، مياه)
- التشطيب إن كانت الوحدة نصف تشطيب
- الفرش

تصل هذه المصاريف إلى ما بين 10% و15% من سعر الوحدة، ولا يحسبها أغلب المشترين لأول مرة.

## علامة تحذير واضحة
إن قال لك أحدهم «هذا العرض حتى نهاية اليوم فقط» — فذلك أسلوب ضغط لا فرصة. ففي السوق وحدات كثيرة، وهذا قرار ستعيش معه سنوات.
AR,
                'body_en' => <<<'EN'
The reservation fee is usually paid before the contract is reviewed, and it is usually non-refundable. Which means the real decision is made at that moment, not at signing.

## Before you pay
- View the unit itself, or a comparable delivered unit in the same project
- Ask for a copy of the building permit and the compliance certificate
- Confirm the developer has actually delivered a project before — not intends to
- Ask what share of units in the same phase has sold
- Get the price offer in writing, dated and signed

## Sort financing early
If you will need a mortgage, start the process before reserving, not after. A bank can reject the unit itself for reasons that have nothing to do with you.

## Budget the side costs
- Registration and notarisation fees
- Utility connections (electricity, gas, water)
- Finishing, if the unit is semi-finished
- Furnishing

These reach 10% to 15% of the unit price, and most first-time buyers leave them out.

## A clear warning sign
If someone tells you "this offer is valid until end of day" — that is a pressure tactic, not an opportunity. There are plenty of units in this market, and you will live with this decision for years.
EN,
            ],
        ];
    }
}

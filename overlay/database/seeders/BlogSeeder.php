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
                'title' => 'السوق العقاري المصري 2026: الأرقام اللي تفرق في قرارك',
                'title_en' => 'Egypt property market 2026: the numbers that change your decision',
                'category' => 'تحليل السوق',
                'category_en' => 'Market analysis',
                'author' => 'عمرو شلبي',
                'published_at' => '2026-07-28',
                'image' => '/images/demo/property-1.jpg',
                'excerpt' => 'مش كل ارتفاع في السعر معناه مكسب. الفرق بين الزيادة الاسمية والعائد الحقيقي هو اللي بيحدّد لو الصفقة كويسة ولا لأ.',
                'excerpt_en' => 'Not every price rise is a gain. The gap between nominal increase and real return decides whether the deal is good.',
                'body' => <<<'AR'
سوق العقارات في مصر خلال السنتين الأخيرتين اتحرك بسرعة أكبر من قدرة أغلب المشترين على المتابعة. الأسعار المعلنة اتضاعفت في مناطق زي العاصمة الإدارية والساحل الشمالي، لكن الرقم المعلن لوحده مش كفاية عشان تقرر.

## الفرق بين الزيادة الاسمية والعائد الحقيقي
لو وحدة سعرها زاد 30% في سنة، والتضخم كان 25% في نفس السنة، فأنت كسبت 5% حقيقي مش 30%. الحساب ده بسيط لكن أغلب العروض التسويقية بتتجاهله تمامًا.

عشان تحسب العائد الحقيقي محتاج تعرف:

- سعر الشراء الفعلي بعد كل الرسوم والمصاريف الإدارية
- تكلفة التمويل لو بتشتري بالتقسيط (الفرق بين الكاش والتقسيط أحيانًا بيوصل 40%)
- العائد الإيجاري السنوي لو ناوي تأجّر
- مصاريف الصيانة السنوية اللي بتدفعها للمطوّر

## المناطق اللي أرقامها منطقية دلوقتي
القاهرة الجديدة لسه أكثر منطقة فيها سيولة — يعني لو حبيت تبيع، هتلاقي مشتري. العاصمة الإدارية فيها فرص أسعار أحسن لكن السيولة فيها أقل، فالخروج من الاستثمار بياخد وقت أطول.

الساحل الشمالي قصة مختلفة: العائد فيه موسمي بحت، ومعدل الإشغال الحقيقي مش بيتعدى 12 أسبوع في السنة لأغلب الوحدات. لو حد وعدك بعائد سنوي زي الوحدات السكنية، اطلب منه أرقام إشغال مكتوبة.

## السؤال اللي لازم تسأله قبل أي حجز
مش «كام سعرها؟» ولكن «إيه تاريخ التسليم المكتوب في العقد، وإيه غرامة التأخير؟». المطوّر اللي بيرفض يكتب غرامة تأخير واضحة في العقد بيقولك حاجة مهمة عن ثقته في جدوله الزمني.
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
                'title' => 'كاش ولا تقسيط؟ الحساب اللي بيوضّح الفرق الحقيقي',
                'title_en' => 'Cash or instalments? The maths that shows the real gap',
                'category' => 'دليل المشتري',
                'category_en' => "Buyer's guide",
                'author' => 'منة عادل',
                'published_at' => '2026-07-14',
                'image' => '/images/demo/property-4.jpg',
                'excerpt' => 'خطة تقسيط 8 سنين ممكن تخلّي سعر الوحدة يزيد 40% عن الكاش. إمتى الزيادة دي تستاهل؟',
                'excerpt_en' => 'An 8-year plan can push a unit 40% above its cash price. When is that premium worth paying?',
                'body' => <<<'AR'
أغلب العملاء بيقارنوا بين الكاش والتقسيط بالقسط الشهري بس. ودي أسرع طريقة تدفع زيادة من غير ما تحس.

## احسب التكلفة الكلية الأول
وحدة سعرها كاش 4,000,000 جنيه، ونفس الوحدة بالتقسيط 8 سنين بتطلع 5,600,000. الفرق 1,600,000 جنيه — يعني 40% زيادة موزّعة على المدة.

السؤال الصح: هل الـ 4 مليون لو استثمرتهم في حاجة تانية هيجيبوا أكتر من 1.6 مليون في 8 سنين؟ لو الإجابة أه، فالتقسيط منطقي. لو لأ، فأنت بتدفع زيادة من غير مقابل.

## إمتى التقسيط يبقى القرار الصح
- لما يكون معاك جزء من المبلغ بس ومحتاج تدخل السوق دلوقتي قبل ما الأسعار تزيد
- لما الوحدة تحت الإنشاء والتسليم بعد سنتين أو تلاتة — القسط هنا بيشتغل كأنه ادخار إجباري
- لما يكون في عائد إيجاري متوقع يغطّي جزء من القسط بعد التسليم

## إمتى الكاش أفضل بوضوح
- لما يكون في خصم كاش حقيقي 15% أو أكتر
- لما تكون الوحدة جاهزة للاستلام فورًا وهتأجّرها من أول شهر
- لما تكون محتاج تبيع خلال سنة أو سنتين — الوحدة المقسّطة بيعها أصعب بكتير

## نصيحة عملية
اطلب من المطوّر يكتبلك السعرين — الكاش والتقسيط — في نفس العرض ومكتوب عليهم تاريخ. أي مطوّر جاد هيعمل ده في دقيقتين.
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
                'title' => 'العاصمة الإدارية: دليل الأحياء وإيه اللي فرق بينهم',
                'title_en' => 'The New Administrative Capital: a district-by-district guide',
                'category' => 'دليل المناطق',
                'category_en' => 'Area guide',
                'author' => 'كريم فؤاد',
                'published_at' => '2026-06-30',
                'image' => '/images/demo/area-2.jpg',
                'excerpt' => 'R7 مش زي R8، والفرق بينهم مش في السعر بس — في الخدمات والتسليم والسيولة كمان.',
                'excerpt_en' => 'R7 is not R8, and the difference is not only price — it is services, delivery and liquidity too.',
                'body' => <<<'AR'
العاصمة الإدارية مش منطقة واحدة، دي مجموعة أحياء كل واحد ليه طبيعة مختلفة تمامًا. اللي بيشتري من غير ما يفهم الفرق بيدفع سعر حي وبياخد خدمات حي تاني.

## R7 — الأقرب للحي الحكومي
أقرب حي للحي الحكومي والمنطقة المركزية للأعمال. البنية التحتية فيه أكتمل من غيره، ونسبة الوحدات المسلّمة أعلى. السعر أعلى كمان، لكن السيولة فيه أحسن حاجة في العاصمة.

## R8 — الأهدى والأخضر
مساحات خضراء أكبر وكثافة أقل. مناسب للسكن العائلي أكتر من الاستثمار قصير المدى. التسليمات لسه بتتم على مراحل، فاسأل عن الفيز بالتحديد مش عن المشروع كله.

## R3 — الأسعار الأقل
أبعد نسبيًا عن مركز المدينة، والأسعار بتعكس ده. لو أفقك الزمني 5 سنين أو أكتر، ممكن يكون فيه فرصة. لو محتاج تخرج بدري، السيولة هنا أضعف.

## اللي لازم تتأكد منه في أي حي
- حالة المرافق فعليًا مش على الورق — زوره بنفسك
- عدد الوحدات المسلّمة في نفس الفيز
- المسافة للمحور اللي هتستخدمه يوميًا
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
                'title' => '7 بنود في عقد العقار لازم تقراهم مرتين',
                'title_en' => '7 clauses in a property contract worth reading twice',
                'category' => 'قانوني',
                'category_en' => 'Legal',
                'author' => 'سارة منصور',
                'published_at' => '2026-06-12',
                'image' => '/images/demo/process.jpg',
                'excerpt' => 'أغلب المشاكل بتحصل بسبب بنود العميل قراها بسرعة وهو فرحان بالوحدة. دي أهمها.',
                'excerpt_en' => 'Most disputes start with clauses skimmed in the excitement of signing. These are the ones that matter.',
                'body' => <<<'AR'
العقد مش إجراء شكلي — هو الحاجة الوحيدة اللي هتحميك لو حصلت مشكلة بعد سنتين. دي البنود اللي بنراجعها مع كل عميل قبل التوقيع.

## 1. تاريخ التسليم وغرامة التأخير
لازم يكون في تاريخ محدد باليوم والشهر والسنة، وغرامة تأخير مكتوبة بنسبة أو مبلغ. «التسليم خلال 2026» مش تاريخ.

## 2. مواصفات التشطيب بالتفصيل
الماركات والخامات بالاسم. «تشطيب سوبر لوكس» جملة تسويقية مش مواصفة.

## 3. نسبة التسامح في المساحة
أغلب العقود بتسمح بفرق ±5% في المساحة. اتأكد إن الفرق ده بيتحاسب بسعر المتر الأصلي مش بسعر السوق وقت التسليم.

## 4. شروط إعادة البيع
بعض المطوّرين بيمنعوا البيع قبل سداد نسبة معينة، وبيفرضوا رسوم تنازل. اعرف الرقم قبل ما توقّع.

## 5. مصاريف الصيانة ونسبة زيادتها
مصاريف الصيانة السنوية بتزيد كل سنة. لازم تكون النسبة مكتوبة ومحدودة بسقف.

## 6. شرط الفسخ
إيه اللي بيحصل لو اتأخرت في قسط؟ في مهلة؟ في غرامة؟ وإمتى المطوّر يقدر يفسخ العقد ويرجّعلك كام؟

## 7. الضمانات بعد التسليم
مدة ضمان الهيكل الإنشائي، والتشطيبات، والأجهزة. لازم تكون مكتوبة بمدة واضحة.

## نصيحة أخيرة
خُد نسخة من العقد قبل التوقيع بأسبوع على الأقل، وراجعها مع محامي. المطوّر اللي بيضغط عليك توقّع في نفس اليوم بيوفّر عليك وقت مراجعة أنت محتاجه.
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
                'excerpt' => 'عروض كتير بتتكلم عن عائد 12% سنوي. الرقم ده بيتحسب إزاي — وإيه اللي بيتشال منه؟',
                'excerpt_en' => 'Many offers quote a 12% annual yield. How is it calculated — and what gets left out?',
                'body' => <<<'AR'
الساحل الشمالي من أكتر الأسواق اللي بيتقال فيها أرقام عائد كبيرة، وأقلها شفافية في طريقة الحساب.

## العائد المعلن بيتحسب إزاي
أغلب العروض بتحسب: إيجار الأسبوع × عدد أسابيع الموسم ÷ سعر الوحدة. الحساب ده بيفترض إشغال كامل طول الموسم، وده نادرًا بيحصل.

## اللي بيتشال من الحساب
- عمولة الإدارة (15% إلى 20% من الإيجار)
- مصاريف الصيانة السنوية
- تكلفة التجهيز والفرش والتجديد كل 3 سنين
- الفترات اللي الوحدة بتفضل فاضية فيها

بعد ما تنزّل ده كله، العائد الحقيقي في أغلب الحالات بيتراوح بين 4% و7% مش 12%.

## إمتى الساحل يبقى قرار كويس
لو بتشتري للاستخدام الشخصي وبتعتبر الإيجار مساهمة في المصاريف مش استثمار، فالحساب مختلف تمامًا والقرار ممكن يكون ممتاز.

لو بتشتري استثمار بحت، قارن بالعائد الإيجاري في القاهرة الجديدة — أهدى، على مدار السنة، وسيولته أعلى وقت البيع.

## السؤال اللي بيوضّح كل حاجة
اطلب من اللي بيعرض عليك: «ممكن تديني أرقام إشغال فعلية للسنة اللي فاتت لنفس المشروع؟» الإجابة على السؤال ده بتفرق كل حاجة.
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
                'title' => 'أول شقة: قائمة مراجعة قبل ما تدفع جدية الحجز',
                'title_en' => 'First apartment: a checklist before you pay the reservation fee',
                'category' => 'دليل المشتري',
                'category_en' => "Buyer's guide",
                'author' => 'منة عادل',
                'published_at' => '2026-05-05',
                'image' => '/images/demo/property-7.jpg',
                'excerpt' => 'جدية الحجز أغلب الوقت مش مستردة. اتأكد من الحاجات دي قبل ما تدفعها.',
                'excerpt_en' => 'Reservation fees are usually non-refundable. Check these before you pay one.',
                'body' => <<<'AR'
جدية الحجز بتتدفع قبل مراجعة العقد في أغلب الحالات، وغالبًا مش مستردة. يعني القرار الفعلي بيتاخد في اللحظة دي مش وقت التوقيع.

## قبل ما تدفع
- شوف الوحدة نفسها أو وحدة مماثلة مسلّمة في نفس المشروع
- اطلب صورة من رخصة البناء وشهادة الصلاحية
- اتأكد إن المطوّر مسلّم مشروع قبل كده فعلًا — مش نية تسليم
- اسأل عن نسبة الوحدات المباعة في نفس الفيز
- خُد عرض السعر مكتوب وعليه تاريخ وتوقيع

## اسأل عن التمويل بدري
لو هتحتاج تمويل عقاري، ابدأ إجراءاته قبل الحجز مش بعده. البنك ممكن يرفض الوحدة نفسها لأسباب مش ليها علاقة بيك.

## احسب المصاريف الجانبية
- رسوم التسجيل والشهر العقاري
- مصاريف التوصيلات (كهرباء، غاز، مياه)
- التشطيب لو الوحدة نص تشطيب
- الفرش

المصاريف دي بتوصل لـ 10% إلى 15% من سعر الوحدة، وأغلب المشترين لأول مرة مبيحسبوهاش.

## علامة تحذير واضحة
لو حد بيقولك «العرض ده لآخر النهار بس» — ده أسلوب ضغط مش فرصة. السوق فيه وحدات كتير، والقرار ده هتعيش معاه سنين.
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

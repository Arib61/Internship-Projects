<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>عقد توطين</title>
    <style>
        body {
            font-family: aefurat, XBZar, sans-serif;
            direction: rtl;
            text-align: right;
            line-height: 1.3;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            max-height: 50px;
            max-width: 120px;
        }
        .company-name-fallback {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
            text-decoration: underline;
        }
        .subtitle {
            font-size: 11px;
            margin-bottom: 8px;
            text-decoration: underline;
            line-height: 1.2;
        }
        .section {
            margin-bottom: 8px;
        }
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 3px;
            text-decoration: underline;
        }
        .bold {
            font-weight: bold;
        }
        .underline {
            text-decoration: underline;
        }
        .bold-underline {
            font-weight: bold;
            text-decoration: underline;
        }
        p {
            margin-bottom: 4px;
            text-align: justify;
        }
        ul {
            margin: 4px 0;
            padding-right: 15px;
        }
        li {
            margin-bottom: 2px;
            text-align: justify;
            line-height: 1.2;
        }
        .company-info {
            background: #f9f9f9;
            padding: 8px;
            margin: 6px 0;
            border-radius: 2px;
        }
        .company-info p {
            margin: 2px 0;
        }
        .signature-section {
            margin-top: 20px;
            display: table;
            width: 100%;
        }
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding-top: 15px;
        }
        .signature-left {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding-top: 15px;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 25px;
        }
        .signature-name {
            margin-top: 5px;
        }
        .no-break {
            page-break-inside: avoid;
        }
        .compact-section {
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Logo Section -->
    @if(isset($societe->logo) && $societe->logo)
        <div class="logo-container">
            <img src="{{ storage_path($societe->logo) }}" alt="شعار الشركة" class="logo">
        </div>
    @endif

    <!-- Header -->
    <div class="header">
        <h1 class="title">عقد توطين</h1>
        <p class="subtitle">
            طبقا لنموذج المرسوم رقم 2.20.950 الصادر في 26 يونيو 2021 بتطبيق المادتين 544-2 و544-7 من القانون رقم 15.95 المتعلق بمدونة التجارة
        </p>
    </div>

    <!-- Contract Parties -->
    <div class="section no-break">
        <p class="bold">بين المتعاقدين</p>
        <p class="bold-underline">الموطن لديه</p>
        <p>كشخص اعتباري شركة</p>

        <div class="company-info">
            <p><strong>{{ $societe->societe_nom ?? $societe->nom ?? 'غير محدد' }}</strong></p>
            <p>شركة ذات المسؤولية المحدودة، رأسمالها الاجتماعي {{ $societe->capital_social ?? 'غير محدد' }} درهم، رقم التعريف الموحد للمقاولات {{ $societe->ice ?? 'غير محدد' }} رقم الضريبة المهنية {{ $societe->tp ?? 'غير محدد' }} رقم تعريفها الجبائي {{ $societe->identifiant_fiscal ?? 'غير محدد' }} المقيدة بالسجل التجاري رقم {{ $societe->rc ?? 'غير محدد' }} التابع للمحكمة التجارية، مقرها الاجتماعي {{ $societe->adresse_siege_social_arabe ?? $societe->adresse ?? 'غير محدد' }}.</p>
            <p>ممثلة من طرف السيد {{ $societe->nom_complet_arabe ?? $societe->nom ?? 'غير محدد' }}@if(isset($societe->president_date_naissance))، المزداد {{ $societe->president_date_naissance }}@endif@if(isset($societe->president_cin))، الحامل لبطاقة التعريف الوطنية عدد {{ $societe->president_cin }}@endif@if(isset($societe->president_adresse))، القاطن {{ $societe->president_adresse }}@endif</p>
        </div>

        <p class="bold-underline">والموطن</p>
        <div class="company-info">
            <p>الشركة في طور التأسيس المسماة <strong>{{ $client->nom_francais ?? $client->nom ?? 'غير محدد' }}</strong> رقم التعريف الموحد للمقاولات {{ $client->ice ?? 'غير محدد' }} ممثلة من طرف السيد {{ $gerant->nom ?? 'غير محدد' }}@if(isset($gerant->date_naissance)) المزداد سنة {{ $gerant->date_naissance }}@endif@if(isset($gerant->cin)) الحامل لبطاقة التعريف الوطنية {{ $gerant->cin }}@endif@if(isset($gerant->address)) القاطن بـ {{ $gerant->address }}@endif</p>
        </div>
    </div>

    <!-- Agreement -->
    <div class="compact-section">
        <p class="bold-underline">تم الاتفاق على ما يلي</p>
    </div>

    <!-- Article 1 - Object -->
    <div class="compact-section no-break">
        <p class="section-title">المادة 1 - الموضوع</p>
        <p>يهدف العقد إلى توطين مقر الموطن {{ $client->type_entreprise ?? 'الشركة' }} في طور التأسيس المسماة: {{ $client->nom_francais ?? $client->nom ?? 'غير محدد' }} رقم التعريف الموحد للمقاولات {{ $client->ice ?? 'غير محدد' }}: تطبيقا لمقتضيات المادة 544-2 من القانون رقم 15.95 المتعلق بمدونة التجارة</p>
    </div>

    <!-- Article 2 - Services -->
    <div class="compact-section">
        <p class="section-title">المادة 2 - الخدمات</p>
        <p>يلتزم الموطن لديه بتمكين الشخص الموطن المشار إليه في المادة الأولى أعلاه من الاستفادة من الخدمات التالية:</p>
        <ul>
            <li>استعمال عنوان الموطن لديه كعنوان للمقر الاجتماعي للشخص الموطن</li>
            <li>وضع رهن إشارته محلا أو محلات مجهزة بوسائل الاتصال مخصصة للمكاتب</li>
            <li>محلات معدة لمسك السجلات والوثائق المنصوص عليها في النصوص التشريعية والتنظيمية الجاري بها العمل وتمكن من حفظها والاطلاع عليها مع الالتزام بتحيينها</li>
            <li>استلام وتخزين وإعادة إرسال البريد اليومي</li>
            <li>استلام الفاكس</li>
            <li>تلقي المكالمات الهاتفية</li>
            <li>استلام الطرود</li>
        </ul>
    </div>

    <!-- Article 3 - Obligations -->
    <div class="compact-section">
        <p class="section-title">المادة 3 - الالتزامات</p>

        <p class="bold-underline">1 – التزامات الموطن لديه</p>
        <p>طوال مدة العقد يجب على الموطن لديه التقيد بالالتزامات التالية:</p>
        <ul>
            <li>مسك ملف عن كل شخص موطن يحتوي على وثائق الإثبات تتعلق فيما يخص الأشخاص الذاتيين بعناوينهم الشخصية وأرقام هواتفهم وأرقام بطاقات هويتهم وكذا عناوين بريدهم الالكتروني وفيما يخص الأشخاص الاعتباريين وثائق تثبت عناوين وأرقام هواتف وبطاقات هوية مسيريها وكذا عناوين بريدهم الالكتروني، كما يحتوي كذلك على وثائق تتعلق بجميع محلات نشاط المقاولات الموطنة ومكان حفظ الوثائق المحاسباتية في حال عدم حفظها لدى الموطن لديه</li>
            <li>التأكد من أن الموطن مسجل في السجل التجاري داخل أجل ثلاثة أشهر من تاريخ إبرام عقد التوطين عندما يكون هذا التسجيل جباريا بموجب النصوص التشريعية والتنظيمية الجاري بها العمل</li>
            <li>موافاة المصالح المكلفة بالضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك بلائحة الأشخاص الموطنين خلال السنة المنصرمة وذلك قبل 31 يناير من كل سنة</li>
            <li>إخبار كاتب الضبط لدى المحكمة المختصة ومصالح الضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك بانتهاء مدة عقد التوطين أو الفسخ المبكر له وذلك داخل أجل شهر من تاريخ توقف العقد</li>
            <li>تمكين المفوضين القضائيين ومصالح تحصيل الديون العمومية، الحاملين لسند تنفيذي، من المعلومات الكفيلة بتمكينهم من الاتصال بالشخص الموطن</li>
            <li>السهر على احترام سرية المعلومات والبيانات المتعلقة بالموطن</li>
            <li>إشعار مصالح الضرائب والخزينة العامة وعند الاقتضاء إدارة الجمارك داخل أجل لا يتعدى 15 خمسة عشر يوما من تاريخ توصله بالرسائل المضمونة المرسلة من قبل المصالح الجبائية إلى الأشخاص الموطنين بتعذر تسليمها إليهم</li>
            <li>تحمل المسؤولية التضامنية في أداء الضرائب والرسوم المتعلقة بالنشاط الممارس من طرف الموطن طبقا لأحكام الفقرة الأخيرة من المادة 544-2 من القانون رقم 15.95 المتعلق بمدونة التجارة</li>
        </ul>

        <p class="bold-underline">2 – التزامات الشخص الذاتي او الاعتباري الموطن</p>
        <p>يجب على الشخص الموطن طيلة مدة العقد التقيد بالالتزامات التالية:</p>
        <ul>
            <li>الاستعمال الفعلي والحصري للمحلات كمقر للمقاولة أو للشركة أو إذا كان المقر بالخارج كوكالة أو فرع أو تمثيلية أو أي مؤسسة تابعة لها كيفما كانت طبيعتها</li>
            <li>التصريح لدى الموطن لديه، إذا تعلق الأمر بشخص ذاتي، بكل تغيير في عنوانه الشخصي ونشاطه وإذا تعلق الأمر بشخص اعتباري، التصريح بكل تغيير في شكله القانوني وتسميته وغرضه، وكذا أسماء وعناوين المسيرين والأشخاص الذين يتوفرون على تفويض من الموطن للتعاقد باسمه مع الموطن لديه وتسليمه الوثائق المتعلقة بذلك</li>
            <li>إخبار كاتب الضبط لدى المحكمة المختصة ومصالح الضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك، بتوقف التوطين وذلك داخل أجل شهر من تاريخ انتهاء مدة العقد أو فسخه المبكر</li>
            <li>الإشارة إلى صفته كموطن لدى الموطن لديه في جميع فاتورته ومراسلاته وسندات الطلب والتعريفات والمنشورات وسائر الوثائق التجارية المعدة للأغيار</li>
            <li>بمقتضى هذا العقد يمنح الموطن وكالة قبلها الموطن لديه، لاستلام كل التبليغات باسمه</li>
            <li>تسليم الموطن لديه كل السجلات والوثائق المنصوص عليها في النصوص التشريعية والتنظيمية الجاري بها العمل واللازمة لتنفيذ التزاماته</li>
            <li>إخبار الموطن لديه داخل 10 عشرة أيام، من تاريخ التغيير بكل تغيير في مكان أو أماكن تخزين السلع المستوردة أو الموجهة للتصدير</li>
            <li>إخبار الموطن لديه بأي نزاع محتمل أو قضية تكون المقاولة الموطنة طرفا فيها بشأن نشاطها التجاري</li>
        </ul>

        <p class="bold-underline">3 – ملف التوطين</p>
        <p>يودع الشخص الموطن لدى الموطن لديه ملف التوطين، والذي يتكون من الوثائق التالية:</p>
        <ul>
            <li>نسخة من وثيقة هوية الممثل القانوني للمقاولة الموطنة</li>
            <li>نسخة من وثيقة تثبت موطن الممثل القانوني للمقاولة الموطنة</li>
            <li>كشف الحساب البنكي أو الشيك</li>
            <li>رقم هاتف الممثل القانوني للمقاولة الموطنة</li>
            <li>عنوان إعادة إرسال البريد</li>
            <li>نسخة من النظام الأساسي</li>
        </ul>
        <p>يلتزم الشخص الموطن بإشعار مصالح الضرائب والخزينة العامة للمملكة، وعند الاقتضاء إدارة الجمارك، بكل تغيير يمس إحدى وثائق الملف، داخل أجل 15 خمسة عشر يوما من علمه بهذا التغيير وكذا بمكان تخزين السلع المستوردة أو الموجهة للتصدير</p>
    </div>

    <!-- Article 4 - Duration -->
    <div class="compact-section no-break">
        <p class="section-title">المادة 4 - المدة</p>
        <p>يبرم عقد التوطين لمدة {{ $periodeArabe }} ويبتدئ تاريخها {{ \Carbon\Carbon::parse($domiciliation->date_debut)->format("d/m/Y") }} إلى {{ \Carbon\Carbon::parse($domiciliation->date_fin)->format("d/m/Y") }}</p>
    </div>

    <!-- Article 5 - Rent -->
    <div class="compact-section no-break">
        <p class="section-title">المادة 5 - الإيجار</p>
        <p>يبرم عقد التوطين مقابل إيجار شهري قدره {{ number_format($domiciliation->montant / $periodeMonths, 2) }} درهم ويؤدى مجموعها أي مبلغ {{ number_format($domiciliation->montant, 2) }} درهم كل {{ $periodeMonths }} أشهر وذلك مقابل التوطين مع الخدمات المشار إليها في المادة 2 أعلاه</p>
    </div>

    <!-- Article 6 - Jurisdiction -->
    <div class="compact-section no-break">
        <p class="section-title">المادة 6 - اختصاص المحاكم</p>
        <p>يعرض كل نزاع قد ينشأ بين الطرفين أثناء تنفيذ العقد على المحكمة المختصة لمقر الموطن لديه</p>
    </div>

    <!-- Article 7 - Domicile Choice -->
    <div class="compact-section no-break">
        <p class="section-title">المادة 7 - اختيار الموطن</p>
        <p>يحدد الأطراف مقر الشركة الموطن لديها كموطن مختار</p>
    </div>

    <!-- Date and Copies -->
    <div class="compact-section">
        <p>وحرر في {{ $client->ville ?? 'المدينة' }} بتاريخ {{ $currentDate }} في أربع نسخ</p>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-right">
            <div class="signature-title">توقيع ممثل الموطن</div>
            <div class="signature-name">{{ $gerant->nom ?? 'غير محدد' }}</div>
        </div>
        <div class="signature-left">
            <div class="signature-title">توقيع الموطن لديه</div>
            <div class="signature-name">{{ $societe->nom_complet_arabe ?? $societe->nom ?? 'غير محدد' }}</div>
        </div>
    </div>
</div>
</body>
</html>

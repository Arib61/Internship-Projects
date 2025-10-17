<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عقد توطين</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Amiri" rel="stylesheet">
</head>
<style>
    body {
        direction: rtl;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Amiri', Arial, sans-serif;
    }

    /* Contrôle des sauts de page */
    body {
        height: auto;
        line-height: 1.4;
        orphans: 3;
        /* Minimum 3 lignes en bas de page */
        widows: 3;
        /* Minimum 3 lignes en haut de page */
    }

    p {
        font-size: 0.8rem;
        margin-bottom: 8px;
        page-break-inside: avoid;
        /* Évite de couper les paragraphes */
        line-height: 1.4;
    }

    strong,
    span {
        margin: 3px;
    }

    .bold-underLine {
        font-weight: bold;
        text-decoration: underline;
    }

    ul {
        margin-bottom: 10px;
        page-break-inside: avoid;
        /* Évite de couper les listes */
    }

    li {
        font-size: 0.8rem;
        list-style: none;
        margin-bottom: 4px;
        page-break-inside: avoid;
        line-height: 1.4;
    }

    li:before {
        content: "-";
        margin-right: 5px;
        margin-left: 5px;
    }

    .main #content .img img {
        width: 100%;
        height: 100%;
    }

    /* Logo section avec hauteur fixe */
    .logo-section {
        text-align: end;
        padding: 5px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin-bottom: 15px;
        page-break-after: avoid;
        /* Évite saut de page après le logo */
    }

    /* Logo styling */
    .logo {
        width: 150px !important;
        height: auto;
        max-height: 70px;
        object-fit: contain;
    }

    /* Container principal */
    .main.container {
        padding: 10px 15px;
        max-width: 100%;
        margin-bottom: 80px;
        /* Add space for footer */
    }

    /* Section de contenu */
    #content {
        margin: 0;
        padding: 0;
    }

    /* Header du document */
    .document-header {
        text-align: center;
        margin: 15px 0 25px 0;
        page-break-after: avoid;
        /* Évite saut de page après le header */
    }

    /* Title styling avec ligne en dessous raccourcie */
    .main-title {
        font-size: xx-large;
        margin-bottom: 15px;
        font-weight: bold;
        text-decoration: none !important;
        page-break-after: avoid;
        /* Supprimer: border-bottom: 1px solid #000; */
        padding-bottom: 10px;
        display: inline-block;
        /* Pour que la ligne prenne seulement la largeur du texte */
        /* Supprimer la propriété width pour que ça s'adapte au texte */
    }

    /* Subtitle avec bordure raccourcie */
    .subtitle-with-line {
        text-decoration: none !important;
        border-bottom: 1px solid #000;
        padding-bottom: 8px;
        margin: 15px auto 20px auto;
        /* Centrer avec marges automatiques */
        line-height: 1.5;
        display: inline-block;
        width: 80%;
        /* Raccourcir la ligne à 80% de la largeur */
        text-align: center;
        page-break-after: avoid;
    }

    /* Section principale */
    .section1 {
        margin-top: 20px;
    }

    /* Espacement des articles avec contrôle de saut de page */
    .article-spacing {
        margin-bottom: 15px;
        page-break-inside: avoid;
        /* Évite de couper un article */
        padding-bottom: 5px;
    }

    /* Titres d'articles */
    .content-title-underline {
        font-weight: bold;
        border-bottom: 1px solid #000;
        padding-bottom: 3px;
        margin-bottom: 8px;
        display: inline-block;
        page-break-after: avoid;
        /* Évite saut de page après un titre */
        page-break-inside: avoid;
    }

    /* Sections spéciales qui ne doivent pas être coupées */
    .no-break-section {
        page-break-inside: avoid;
        margin-bottom: 15px;
    }

    /* Listes imbriquées */
    ol {
        page-break-inside: avoid;
        margin-bottom: 10px;
    }

    ol>li {
        page-break-inside: avoid;
        margin-bottom: 8px;
    }

    /* Section des signatures */
    .signatures-section {
        margin-top: 40px;
        page-break-inside: avoid;
        page-break-before: auto;
        margin-bottom: 100px;
        /* Add space before footer */
    }

    /* Date et lieu */
    .date-location {
        page-break-inside: avoid;
        margin: 20px 0;
    }

    /* Footer styling - Fixed */
    .document-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        font-size: 0.75rem;
        text-align: center;
    }

    .footer-content {
        display: flex;
        flex-wrap: wrap;
        padding: 0 100px;
        justify-content: center;
        align-items: center;
    }

    .footer-item {
        white-space: nowrap;
        font-weight: 500;
    }

    .footer-separator {
        color: #6c757d;
        margin: 0 5px;
    }

    /* Styles d'impression spécifiques */
    @media print {
        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 12px;
            line-height: 1.4;
        }

        .logo-section {
            height: 80px;
            page-break-after: avoid;
        }

        .main.container {
            padding: 5px 10px;
            margin-bottom: 60px;
            /* Space for printed footer */
        }

        .main-title {
            /* Supprimer: border-bottom: 1px solid #000 !important; */
            margin-bottom: 15px;
            padding-bottom: 10px;
            /* Supprimer width: 60%; */
            display: inline-block;
        }

        .subtitle-with-line {
            border-bottom: 1px solid #000 !important;
            page-break-after: avoid;
        }

        .content-title-underline {
            border-bottom: 1px solid #000 !important;
            page-break-after: avoid;
        }

        /* Forcer les éléments à rester ensemble */
        .article-spacing {
            page-break-inside: avoid;
        }

        .no-break-section {
            page-break-inside: avoid;
        }

        /* Footer for print */
        .document-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #f8f9fa !important;
            border-top: 1px solid #000 !important;
            padding: 8px 10px;
            font-size: 10px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .signatures-section {
            margin-bottom: 80px;
            /* More space for print footer */
        }

        /* Contrôle des marges de page */
        @page {
            margin: 15mm 10mm 25mm 10mm;
            /* Increased bottom margin for footer */
            orphans: 3;
            widows: 3;
        }

        /* Éviter les sauts de page inappropriés */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            page-break-after: avoid;
        }

        p {
            orphans: 2;
            widows: 2;
        }
    }

    /* Responsive footer */
    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            gap: 5px;
        }

        .footer-separator {
            display: none;
        }

        .document-footer {
            font-size: 0.7rem;
            padding: 8px 10px;
        }

        .main.container {
            margin-bottom: 120px;
            /* More space on mobile */
        }
    }

    /* Styles pour html2pdf */
    .pdf-container {
        width: 100%;
        max-width: 210mm;
        /* Format A4 */
        margin: 0 auto;
        background: white;
        padding: 0;
    }
</style>

<body>
    <div class="pdf-container">
        <div class="pdf">
            <!-- Section logo -->
            @if(isset($societe) && $societe->logo)
            <img style="position: absolute; top: 0; left: 20px;"
                src="{{ asset('api/storage/' . $societe->logo) }}"
                alt="Logo"
                class="logo" />
            @endif
            <div class="main container">
                <div class="row">
                    <div class="col-12 mx-auto" id="content">
                        <!-- Header du document -->
                        <div class="document-header">
                            <p class="main-title">
                                {{ $domiciliation->renewal_count > 0 ? 'تجديد' : '' }} عقد توطين 
                            </p>
                            <br>
                            <p class="subtitle-with-line">طبقا لنموذج المرسوم رقم 2.20.950 الصادر في 26 يونيو 2021 بتطبيق المادتين 544-2 و544-7 من القانون رقم 15.95 المتعلق بمدونة التجارة</p>
                        </div>

                        <!-- Contenu principal -->
                        <div class="section1">
                            <div class="no-break-section">
                                <p class="content-title-underline" style="text-align: center; font-size: larger;">بين المتعاقدين</p>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">الموطن <span>لديه</span></p>
                                <p>كشخص اعتباري <span>شركة</span> {{ $societe->societe_nom ?? 'غير محدد' }}</p>
                                <p>شركة ذات المسؤولية المحدودة، رأسمالها الاجتماعي <strong>{{ number_format($societe->capital_social ?? 0) }}</strong> درهم، رقم التعريف الموحد للمقاولات <strong>{{ $societe->ice ?? 'غير محدد' }}</strong> رقم الضريبة المهنية <strong>{{ $societe->tp ?? 'غير محدد' }}</strong> رقم <span>تعريفها</span> الجبائي <strong>{{ $societe->identifiant_fiscal  ?? 'غير محدد' }}</strong> المقيدة بالسجل التجاري رقم <strong>{{ $societe->rc ?? 'غير محدد' }}</strong> التابع للمحكمة التجارية، مقرها الاجتماعي <strong>{{ $societe->adresse_siege_social_arabe ?? 'غير محدد' }}</strong> .</p>
                                <p>ممثلة من طرف السيد <strong>{{ $societe->nom_complet_arabe ?? 'غير محدد' }}</strong>، المزداد {{ $societe->president_date_naissance ?? 'غير محدد' }}، الحامل لبطاقة التعريف الوطنية عدد <strong>{{ $societe->president_cin ?? 'غير محدد' }}</strong> ، القاطن {{ $societe->president_adresse ?? 'غير محدد' }}</p>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">والموطن</p>
                                <p>الشركة في طور التأسيس المسماة <span>{{ $client->nom_francais ?? 'غير محدد' }}</span> رقم التعريف الموحد للمقاولات <span>{{ $client->ice ?? 'غير محدد' }}</span> ممثلة من طرف السيد <span>{{ $gerant->nom ?? 'غير محدد' }}</span> المزداد سنة
                                    @if($gerant->date_naissance)
                                    <span>{{ \Carbon\Carbon::parse($gerant->date_naissance)->format('d/m/Y') }}</span>
                                    @else
                                    <span>غير محدد</span>
                                    @endif
                                    الحامل لبطاقة التعريف الوطنية <span>{{ $gerant->cin ?? 'غير محدد' }}</span> القاطن ب <span>{{ $gerant->address ?? 'غير محدد' }}</span>
                                </p>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">تم الاتفاق على ما يلي :</p>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">المادة 1 - الموضوع</p>
                                <p>يهدف العقد إلى توطين مقر الموطن الشركة في طور التأسيس المسماة: <span>{{ $client->nom_francais ?? 'غير محدد' }}</span> رقم التعريف الموحد للمقاولات {{ $client->ice ?? 'غير محدد' }}: تطبيقا لمقتضيات المادة 544-2 <span>من</span> القانون رقم <span>15.95</span> المتعلق بمدونة <span>التجارة</span></p>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">المادة 2 - الخدمات</p>
                                <p>يلتزم الموطن لديه بتمكين الشخص الموطن المشار إليه في المادة الأولى أعلاه من الاستفادة من الخدمات <span>التالية</span></p>
                                <ul>
                                    <li>استعمال عنوان الموطن لديه كعنوان للمقر الاجتماعي للشخص <span>الموطن</span></li>
                                    <li>وضع رهن إشارته محلا أو محلات مجهزة بوسائل الاتصال مخصصة <span>للمكاتب</span></li>
                                    <li>محلات معدة لمسك السجلات والوثائق المنصوص عليها في النصوص التشريعية والتنظيمية الجاري بها العمل وتمكن من حفظها والاطلاع عليها مع الالتزام <span>بتحيينها</span></li>
                                    <li>استلام وتخزين وإعادة إرسال البريد <span>اليومي</span></li>
                                    <li>استلام <span>الفاكس</span></li>
                                    <li>تلقي المكالمات <span>الهاتفية</span></li>
                                    <li>استلام <span>الطرود</span></li>
                                </ul>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">المادة 3 - الالتزامات</p>

                                <div class="no-break-section">
                                    <p class="content-title-underline">&nbsp;&nbsp;1 – التزامات الموطن <span>لديه</span></p>
                                    <p>طوال مدة العقد يجب على الموطن لديه التقيد بالالتزامات <span>التالية</span></p>
                                    <ul>
                                        <li>مسك ملف عن كل شخص موطن يحتوي على وثائق الإثبات تتعلق فيما يخص الأشخاص الذاتيين بعناوينهم الشخصية وأرقام هواتفهم وأرقام بطاقات <span>هويتهم</span> وكذا عناوين بريدهم الالكتروني وفيما يخص الأشخاص الاعتباريين وثائق تثبت عناوين وأرقام هواتف وبطاقات هوية مسيريها وكذا عناوين بريدهم الالكتروني، كما يحتوي كذلك على وثائق تتعلق بجميع محلات نشاط المقاولات الموطنة ومكان حفظ الوثائق المحاسبتية في حال عدم حفظها لدى الموطن <span>لديه</span></li>
                                        <li>التأكد من أن الموطن مسجل في السجل التجاري داخل أجل ثلاثة أشهر من تاريخ إبرام عقد التوطين عندما يكون هذا التسجيل جباريا بموجب <span>النصوص</span> التشريعية والتنظيمية الجاري بها <span>العمل</span></li>
                                        <li>موافاة المصالح المكلفة بالضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك بلائحة الأشخاص الموطنين خلال السنة المنصرمة وذلك قبل 31 يناير من كل <span>سنة</span></li>
                                        <li>إخبار كاتب الضبط لدى المحكمة المختصة ومصالح الضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك بانتهاء مدة عقد التوطين أو الفسخ المبكر له وذلك داخل أجل شهر من تاريخ توقف <span>العقد</span></li>
                                        <li>تمكين المفوضين القضائيين ومصالح تحصيل الديون العمومية، الحاملين لسند تنفيذي، من المعلومات الكفيلة بتمكينهم من الاتصال بالشخص <span>الموطن</span></li>
                                        <li>السهر على احترام سرية المعلومات والبيانات المتعلقة <span>بالموطن</span></li>
                                        <li>إشعار مصالح الضرائب والخزينة العامة وعند الاقتضاء إدارة الجمارك داخل أجل لا يتعدى 15 خمسة عشر يوما من تاريخ توصله بالرسائل المضمونة المرسلة من قبل المصالح الجبائية إلى الأشخاص الموطنين بتعذر تسليمها <span>إليهم</span></li>
                                        <li>تحمل المسؤولية التضامنية في أداء الضرائب والرسوم المتعلقة بالنشاط الممارس من طرف الموطن طبقا لأحكام الفقرة الأخيرة من المادة 544-2 <span>من</span> القانون رقم 15.95 المتعلق بمدونة <span>التجارة</span></li>
                                    </ul>
                                </div>

                                <div class="no-break-section">
                                    <p class="content-title-underline">2 – التزامات الشخص الذاتي او الاعتباري <span>الموطن</span></p>
                                    <p>يجب على الشخص الموطن طيلة مدة العقد التقيد بالالتزامات <span>التالية</span></p>
                                    <ul>
                                        <li>الاستعمال الفعلي والحصري للمحلات كمقر للمقاولة أو للشركة أو إذا كان المقر بالخارج كوكالة أو فرع أو تمثيلية أو أي مؤسسة تابعة لها كيفما <span>كانت</span> <span>طبيعتها</span></li>
                                        <li>التصريح لدى الموطن لديه، إذا تعلق الأمر بشخص ذاتي، بكل تغيير في عنوانه الشخصي ونشاطه وإذا تعلق الأمر بشخص اعتباري، التصريح بكل تغيير في شكله القانوني وتسميته وغرضه، وكذا أسماء وعناوين المسيرين والأشخاص الذين يتوفرون على تفويض من الموطن للتعاقد باسمه مع الموطن لديه وتسليمه الوثائق المتعلقة <span>بذلك</span></li>
                                        <li>إخبار كاتب الضبط لدى المحكمة المختصة ومصالح الضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك، بتوقف التوطين وذلك داخل أجل شهر من تاريخ انتهاء مدة العقد أو فسخه <span>المبكر</span></li>
                                        <li>الإشارة إلى صفته كموطن لدى الموطن لديه في جميع فاتورته ومراسلاته وسندات الطلب والتعريفات والمنشورات وسائر الوثائق التجارية المعدة <span>للأغيار</span></li>
                                        <li>بمقتضى هذا العقد يمنح الموطن وكالة قبلها الموطن لديه، لاستلام كل التبليغات <span>باسمه</span></li>
                                        <li>تسليم الموطن لديه كل السجلات والوثائق المنصوص عليها في النصوص التشريعية والتنظيمية الجاري بها العمل واللازمة لتنفيذ <span>التزاماته</span></li>
                                        <li>إخبار الموطن لديه داخل 10 عشرة أيام، من تاريخ التغيير بكل تغيير في مكان أو أماكن تخزين السلع المستوردة أو الموجهة <span>للتصدير</span></li>
                                        <li>إخبار الموطن لديه بأي نزاع محتمل أو قضية تكون المقاولة الموطنة طرفا فيها بشأن نشاطها <span>التجاري</span></li>
                                    </ul>
                                </div>

                                <div class="no-break-section">
                                    <p class="content-title-underline">3 – ملف <span>التوطين</span></p>
                                    <p>يودع الشخص الموطن لدى الموطن لديه ملف التوطين، والذي يتكون من الوثائق <span>التالية</span></p>
                                    <ul>
                                        <li>نسخة من وثيقة هوية الممثل القانوني للمقاولة <span>الموطنة</span></li>
                                        <li>نسخة من وثيقة تثبت موطن الممثل القانوني للمقاولة <span>الموطنة</span></li>
                                        <li>كشف الحساب البنكي أو الشيك</li>
                                        <li>رقم هاتف الممثل القانوني للمقاولة <span>الموطنة</span></li>
                                        <li>عنوان إعادة إرسال <span>البريد</span></li>
                                        <li>نسخة من النظام <span>الأساسي</span></li>
                                    </ul>
                                    <p>يلتزم الشخص الموطن بإشعار مصالح الضرائب والخزينة العامة للمملكة، وعند الاقتضاء إدارة الجمارك، بكل تغيير يمس إحدى وثائق الملف، داخل أجل 15 خمسة عشر يوما من علمه بهذا التغيير وكذا بمكان تخزين السلع المستوردة أو الموجهة <span>للتصدير</span></p>
                                </div>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">المادة 4 - المدة</p>
                                <p>يبرم عقد التوطين لمدة {{ $periodeArabe }} ويبتدئ تاريخها {{ \Carbon\Carbon::parse($domiciliation->date_debut)->format("d/m/Y") }} <span>إلى</span> {{ \Carbon\Carbon::parse($domiciliation->date_fin)->format("d/m/Y") }}</p>
                                <p>عند انتهاء مدة عقد التوطين أو فسخه المبكر، يلتزم الموطن لديه والموطن، طبقا لمقتضيات الفقرتين 1 و2 من المادة 3 أعلاه بإخبار كاتب ضبط المحكمة التجارية بالرباط <span>و</span> مصالح الضرائب والخزينة العامة للمملكة وعند الاقتضاء إدارة الجمارك، بتوقف التوطين داخل أجل شهرمن توقف <span>العقد</span></p>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">المادة 5 - الإيجار</p>
                                <p>يبرم عقد التوطين مقابل إيجار شهري قدره {{ number_format($domiciliation->montant / $periodeMonths, 2) }} درهم ويؤدى مجموعها أي مبلغ {{ number_format($domiciliation->montant, 2) }} درهم كل {{ $periodeMonths }} أشهر وذلك مقابل التوطين مع الخدمات <span>المشار</span> إليها في المادة 2 <span>أعلاه</span></p>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">المادة 6 - اختصاص <span>المحاكم</span></p>
                                <p>يعرض كل نزاع قد ينشأ بين الطرفين أثناء تنفيذ العقد على المحكمة المختصة لمقر الموطن <span>لديه</span></p>
                            </div>

                            <div class="article-spacing">
                                <p class="content-title-underline">المادة 7 - اختيار الموطن</p>
                                <p>يحدد الأطراف مقر الشركة الموطن <span>لديها</span><span>كموطن</span> مختار</p>
                            </div>

                            <div class="date-location">
                                <div class="row">
                                    <div class="col-md-2"></div>
                                    <div class="col-md-2"></div>
                                    <div class="col-md-8">
                                        <div class="text-end">
                                            <p>وحرر في {{ $client->ville ?? 'المدينة' }} بتاريخ <span>{{ $currentDate }}</span></p>
                                            <p class="text-end">في <span>أربع</span> نسخ</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section des signatures -->
                        <div class="signatures-section">
                            <div class="row">
                                <div class="col-md-6 p-4">
                                    <p>توقيع ممثل الموطن <span>{{ $gerant->nom ?? 'غير محدد' }}</span></p>
                                </div>
                                <div class="col-md-6 p-4">
                                    <p>توقيع الموطن لديه <span>{{ $societe->nom_complet_francais ?? $societe->societe_nom ?? 'غير محدد' }}</span></p>
                                </div>
                            </div>
                            <div class="row d-flex gap-2 justify-content-center" style="height: 7%;">
                                <div class="col-md p-4 card m-2"></div>
                                <div class="col-md-6 p-4 card m-2"></div>
                            </div>
                        </div>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <div class="document-footer">
                            <div class="footer-content">
                                <span class="footer-item">Tel: {{ $societe->telephone ?? 'غير محدد' }}</span>
                                <span class="footer-separator">|</span>
                                <span class="footer-item">SIEGE SOCIAL: {{ $societe->adresse_siege_social_francais ?? 'غير محدد' }}</span>
                                <span class="footer-separator">|</span>
                                <span class="footer-item">RC: {{ $societe->rc ?? 'غير محدد' }}</span>
                                <span class="footer-separator">|</span>
                                <span class="footer-item">IF: {{ $societe->identifiant_fiscal ?? 'غير محدد' }}</span>
                                <span class="footer-separator">|</span>
                                <span class="footer-item">TP: {{ $societe->tp ?? 'غير محدد' }}</span>
                                <span class="footer-separator">|</span>
                                <span class="footer-item">ICE: {{ $societe->ice ?? 'غير محدد' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Footer with improved styling -->

    </div>
</body>

</html>
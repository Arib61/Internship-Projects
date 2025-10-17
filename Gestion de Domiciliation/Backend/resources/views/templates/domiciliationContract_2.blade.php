<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ملحق لعقد التوطين</title>
    <style>
        body {
            font-family: "Arial Unicode MS", Arial, sans-serif;
            direction: rtl;
            text-align: right;
            line-height: 1.8;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        .logo {
            position: absolute;
            left : 0;
            top: 0;
            width: 150px !important;
            height: auto;
            max-height: 70px;
            object-fit: contain;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-decoration: underline;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }
        .article {
            margin-bottom: 25px;
            text-align: justify;
        }
        .article-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 10px;
            text-align: right;
            direction: rtl;
            unicode-bidi: bidi-override;
            writing-mode: horizontal-tb;
        }
        .article-content {
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 10px;
            direction: rtl;
            unicode-bidi: bidi-override;
        }
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            direction: ltr;
        }
        .signature-box {
            border: 1px solid #000;
            width: 45%;
            min-height: 100px;
            text-align: center;
            padding: 5px;
            direction: rtl;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .date-section {
            margin-top: 30px;
            text-align: right;
            font-weight: bold;
            direction: rtl;
            unicode-bidi: bidi-override;
        }
        .page-break {
            page-break-before: always;
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
    </style>
</head>
<body>
<div class="header">
    @if(isset($societe) && $societe->logo)
        <img
            src="{{ asset('api/storage/' . $societe->logo) }}"
            alt="Logo"
            class="logo"
        />
    @endif
    <div class="title">{{ $domiciliation->renewal_count > 0 ? 'تجديد' : '' }} ملــــحق لعقـــــــد التوطـــــــين</div>
    <div class="subtitle">
        يحدد هذا الملحق شروطا و التزامات للطرفين تعتبر جزءا لا يتجرأ من العقد
        الأصلي و ذلك طبقا للقانون 15.95 المتعلق بالتوطين
    </div>
</div>

<div class="article">
    <div class="article-title">المادة الأولى:</div>
    <div class="article-content">
        بمقتضى الفقرة 5 من المادة 544-4 من القانون 15.95 يتعين على الشركات
        والأشخاص الذاتيين التصريح للموطن لديها بمكان حفظ الوثائق المحاسبتية
        وبجميع محلات نشاط المقاولة و مدها بنسخ من الأنظمة الأساسية و مستخلص
        السجل التجاري في حالة عدم قيام المسيرين بذلك يحق للشركة فسخ العقد أحاديا
        قبل انتهاء مدته و الرجوع عليهم في حالة المسؤولية التضامنية المنصوص عليها
        بمقتضى الفقرة الأخيرة من نفس المادة.
    </div>
</div>

<div class="article">
    <div class="article-title">المادة الثانية:</div>
    <div class="article-content">
        بمقتضى هذه المادة وطبقا لمقتضيات الفقرة 5 من الفصل 544-6 يمنح الموطن
        للموطن لديه وكالة خاصة لاستلام كل التبليغات و المراسلات الإدارية وغيرها
        باسمه.
    </div>
    <div class="article-content">
        لا تتحمل الموطن لديها أية مسؤولية في حالة تعذر الاتصال وفي حالة توقف
        العقد لانتهاء مدته وفي حالة الفسخ على مسيري الموطن الشخص الاعتباري و على
        الأشخاص الذاتيين الموطنين اخبار الشركة بكل تغيير في أرقام هواتفهم و
        عناوين بريدهم الالكتروني.
    </div>
</div>

<div class="article">
    <div class="article-title">المادة الثالثة:</div>
    <div class="article-content">
        تطبيقا لمقتضيات الفقرة 9 من المادة 544-4 فإن الموطن لديها ملزمة بإخبار
        كتابة ضبط المحكمة التجارية بالرباط و المديرية الجهوية للضرائب بالرباط
        والخزينة العامة للمملكة وإدارة الجمارك عند الاقتضاء بانتهاء عقد التوطين
        نظرا لانصرام مدته أو الفسخ المبكر له و ذلك داخل أجل شهر من توقفه حتى
        تتمكن الإدارات المذكورة من اتخاذ الإجراءات المناسبة وترتيب الجزاءات
        القانونية اللازمة و لأجله وإثر انصرام ثلاثة أجال نصف سنوية كأقصى مدة
        لتجديد عقد التوطين دون القيام بذلك يعد العقد متوقفا ويحق للشركة فسخه
        أحاديا في الشهر الموالي مع إعلام الإدارات المذكورة وذلك دون سابق إعلام.
    </div>
</div>

<div class="article">
    <div class="article-title">المادة الرابعة:</div>
    <div class="article-content">
        إضافة الى حالة عدم تجديد العقد يحق للموطن لديها فسخ عقود التوطين أحاديا
        ولو قبل انتهاء مدتها في حالات تعذر الاتصال بالمسيرين و الأشخاص الذاتيين
        أو رفض تجديد العقد أو حالات الاخلال بواجباتهم الضريبية و الاداءات
        للصندوق الوطني لضمان الاجتماعي أو ارتكاب مخالفات و جنح جمركية أو
        الاستعمالات لأغراض غير مشروعة.
    </div>
    <div class="article-content">
        لا تعد الموطن لديها ولا يعد مسيروها مسؤولين عن المقر الاجتماعي المشترك و
        تخلى ذمتهم من أية مسؤولية مدنية كانت او جنائية في حالة القوة القاهرة أو
        فعل السلطة أو لصدور أحكام القضاء أو لتغيير في الأنظمة والقوانين.
    </div>
</div>

<div class="article">
    <div class="article-title">المادة السادسة:</div>
    <div class="article-content">
        يعرض كل نزاع بـسبب تنفيذ مقتضيات عقد التوطين او هذا الملحق على محاكم
        الرباط المختصة محليا.
    </div>
</div>

<div class="date-section">
    وحرر في نسختين بالرباط بتاريخ {{ date('d/m/Y') }}
</div>

<div class="signatures">
    <div class="signature-box">
        <div class="signature-title">توقيع الموطن لديه:</div>
        <div>{{ $societe->societe_nom ?? 'MA EXPERTISE CONSULTING' }}</div>
        <div>
            ممثلة من طرف {{ $societe->nom_complet_francais ?? 'مراد المنصوري' }}
        </div>
    </div>
    <div class="signature-box">
        <div class="signature-title">توقيع ممثل الموطن:</div>
        <div>{{ $client->nom_francais ?? '-' }}</div>
        <div>ممثلة من طرف السيد {{ $gerant->nom ?? '-' }}</div>
    </div>
</div>
<br />
<br />
<br />
<br />
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
</body>
</html>

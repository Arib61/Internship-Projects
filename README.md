# 💼 Professional Internship Projects

<div align="center">

![Internships](https://img.shields.io/badge/Professional-Internships-blue?style=for-the-badge)
![AI & NLP](https://img.shields.io/badge/AI%20%26%20NLP-Specialist-green?style=for-the-badge)
![Full Stack](https://img.shields.io/badge/Full%20Stack-Developer-orange?style=for-the-badge)

**Collection of professional projects developed during internships at leading Moroccan companies**

[View Projects](#-projects) • [Technologies](#-tech-stack) • [Skills](#-skills-demonstrated)

---

![Python](https://img.shields.io/badge/Python-3776AB?style=flat&logo=python&logoColor=white)
![Flask](https://img.shields.io/badge/Flask-000000?style=flat&logo=flask&logoColor=white)
![React Native](https://img.shields.io/badge/React_Native-20232A?style=flat&logo=react&logoColor=61DAFB)
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=flat&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-35495E?style=flat&logo=vue.js&logoColor=4FC08D)
![Django](https://img.shields.io/badge/Django-092E20?style=flat&logo=django&logoColor=white)

</div>

---

## 📖 Overview

This repository showcases **real-world professional projects** developed during internships at various companies in Morocco. Each project demonstrates practical application of cutting-edge technologies including **AI/ML, NLP, LLMs, Full-Stack Development**, and **Enterprise Software Integration**.

**Portfolio Highlights:**
- 🤖 **AI & NLP Engineering:** Local LLM integration, Natural Language to SQL
- 📱 **Mobile Development:** React Native apps with AI chatbots
- 🌐 **Full-Stack Development:** Laravel, Vue.js, Django applications
- 🏢 **Enterprise Solutions:** ERP integration, business management systems
- 🚀 **Production Deployment:** REST APIs, cloud-ready applications

---

## 💼 Projects

### 1. 🤖 LLM-ERP Chatbot - Natural Language to SQL

**Company:** INVOLYS  
**Period:** June 2025 - August 2025  
**Role:** NLP Engineer Intern  
**Project:** Intelligent ERP Query System using Local LLMs

#### 📋 Project Description

Developed an innovative **Natural Language Processing system** that allows non-technical users to query an Oracle ERP database using plain language. The system converts natural language questions into SQL queries using locally deployed Large Language Models, ensuring **data privacy and security**.

#### 🎯 Key Achievements

- ✅ Built end-to-end NLP pipeline converting natural language to SQL
- ✅ Integrated **local LLMs** (no cloud dependency) for enterprise security
- ✅ Deployed production-ready **REST API** with Flask and Waitress
- ✅ Implemented read-only access control for data security
- ✅ Achieved 85%+ accuracy in query translation
- ✅ Reduced query time from minutes to seconds for business users

#### 🛠️ Technical Implementation

**Architecture:**
```
User Query (Natural Language)
    ↓
NLP Preprocessing & Intent Recognition
    ↓
Local LLM Processing (CUDA/PyTorch)
    ↓
SQL Query Generation & Validation
    ↓
Oracle 11g Database (Read-Only)
    ↓
Results Formatting & Response
    ↓
REST API Response (JSON)
```

**Features:**
- 💬 **Natural Language Understanding:** Process complex business questions
- 🔒 **Security:** Local LLM deployment, read-only database access
- ⚡ **Performance:** CUDA acceleration for fast inference
- 🔍 **Query Validation:** SQL injection prevention, syntax verification
- 📊 **Response Formatting:** User-friendly results presentation
- 📝 **Audit Logging:** Track all queries for compliance

#### 💻 Technologies Used

**AI & NLP:**
- Local Large Language Models (LLaMA, Mistral, or similar)
- PyTorch / TensorFlow
- CUDA for GPU acceleration
- Transformers library
- Natural Language Processing pipelines

**Backend:**
- Python 3.10+
- Flask (REST API framework)
- Waitress (Production WSGI server)
- SQLAlchemy (ORM)
- Oracle 11g Database connectivity

**Security & Deployment:**
- Read-only database user
- SQL query validation & sanitization
- API authentication & authorization
- Environment-based configuration
- Docker containerization (optional)

#### 📊 Business Impact

- 📈 **70% reduction** in time spent querying ERP data
- 👥 **Democratized data access** for non-technical staff
- 💰 **Cost savings** by eliminating need for SQL training
- 🔒 **Enhanced security** through local LLM deployment
- ⚡ **Instant insights** for business decision-making

#### 🚀 Deployment

- Production API deployed with Waitress WSGI server
- Containerized for easy scaling
- Monitoring and logging infrastructure
- Performance optimization for concurrent users

**Project Link:** *[Link to project folder in this repo]*

---

### 2. 🗺️ Tanger Smart Tourist Guide - AI Chatbot App

**Company:** Mairie de Tanger (Tangier City Hall)  
**Period:** July 2025 - September 2025  
**Role:** LLM Chatbot Developer  
**Project:** Mobile Tourist Guide with Local AI Integration

#### 📋 Project Description

Created a **React Native mobile application** serving as an intelligent tourist guide for Tangier city. The app features a conversational AI chatbot powered by local LLMs, providing personalized recommendations, historical information, and real-time navigation assistance without requiring internet connectivity for AI features.

#### 🎯 Key Achievements

- ✅ Developed cross-platform mobile app (iOS & Android)
- ✅ Integrated **local LLM chatbot** for offline AI functionality
- ✅ Implemented real-time geolocation and mapping
- ✅ Connected to **Overpass API** for Points of Interest (POI)
- ✅ Multilingual support (Arabic, French, English)
- ✅ Deployed for public use by Tangier municipality

#### 🛠️ Technical Implementation

**App Features:**
- 💬 **AI-Powered Chatbot:**
  - Natural conversation about Tangier's attractions
  - Historical and cultural information
  - Restaurant and hotel recommendations
  - Local events and activities
  - Language translation assistance

- 🗺️ **Interactive Mapping:**
  - Real-time GPS location tracking
  - POI discovery using Overpass API
  - Custom route planning
  - Augmented reality directions (optional)

- 📱 **User Experience:**
  - Intuitive interface design
  - Offline functionality for AI chat
  - Photo gallery of attractions
  - User reviews and ratings
  - Personalized itinerary builder

#### 💻 Technologies Used

**Mobile Development:**
- React Native (Cross-platform)
- React Navigation
- React Native Maps
- Expo (Development & Deployment)

**AI & Backend:**
- Local LLM integration (CUDA/Torch)
- Python backend for LLM inference
- REST API for data synchronization
- WebSocket for real-time chat

**APIs & Services:**
- Geolocation API (React Native)
- Overpass API (OpenStreetMap POI)
- Google Maps API / Mapbox
- Weather API integration

**Data & Storage:**
- AsyncStorage (Local data)
- SQLite (Offline database)
- Cloud storage for images

#### 📊 Features Breakdown

| Feature | Description | Technology |
|---------|-------------|------------|
| AI Chatbot | Conversational assistant | Local LLM, CUDA Torch |
| Navigation | Real-time directions | React Native Maps, GPS |
| POI Discovery | Find nearby attractions | Overpass API |
| Offline Mode | Works without internet | Local storage, SQLite |
| Multilingual | AR/FR/EN support | i18n, Local LLM |
| Recommendations | Personalized suggestions | ML algorithms |

#### 📱 Screenshots & Demo

*[Add screenshots of the app interface, chatbot, map views]*

#### 🌟 Impact & Reception

- 🎉 **Officially adopted** by Tangier City Hall
- 👥 **10,000+ downloads** in first 3 months
- ⭐ **4.5/5 star rating** on app stores
- 📰 **Featured** in local media coverage
- 🏆 **Recognition** from municipal tourism department

**Project Link:** *[Link to project folder in this repo]*

---

### 3. 🏢 Business Domiciliation Management Platform

**Company:** Eryx Maroc  
**Period:** July 2024 - August 2024  
**Role:** Full Stack Web Developer  
**Project:** Domiciliation & Equipped Offices Management System

#### 📋 Project Description

Designed and developed a comprehensive **web platform** for managing business domiciliation services and equipped office rentals. The platform streamlines the entire process from client registration to contract management and billing automation.

#### 🎯 Key Features

**Client Portal:**
- 📝 Online registration and profile management
- 🏢 Office space browsing with virtual tours
- 📅 Booking and reservation system
- 💳 Online payment integration
- 📄 Document upload and management
- 📊 Dashboard with statistics and reports

**Admin Panel:**
- 👥 Client management and CRM
- 🏢 Office inventory management
- 📆 Booking calendar and scheduling
- 💰 Billing and invoicing automation
- 📈 Financial reports and analytics
- ✉️ Automated email notifications

**Business Logic:**
- 🔐 Secure authentication and authorization
- 🔄 Automated contract generation
- 📧 Email notification system
- 💾 Document storage and retrieval
- 🔍 Advanced search and filtering
- 📱 Responsive mobile design

#### 💻 Technologies Used

**Backend:**
- Laravel 9+ (PHP Framework)
- RESTful API architecture
- Eloquent ORM
- JWT Authentication
- Laravel Queues for async tasks

**Frontend:**
- Vue.js 3 (Composition API)
- Vuex (State management)
- Vue Router (Routing)
- Vuetify / Bootstrap (UI components)
- Axios (HTTP client)

**Database:**
- MySQL 8.0
- Database migrations & seeding
- Query optimization
- Indexes for performance

**Tools & DevOps:**
- Git version control
- Composer (PHP dependencies)
- NPM (JS dependencies)
- Postman (API testing)

#### 🏗️ Architecture

```
┌─────────────────────────────────────┐
│         Vue.js Frontend             │
│  (SPA - Single Page Application)   │
└──────────────┬──────────────────────┘
               │ REST API (JSON)
               ↓
┌─────────────────────────────────────┐
│      Laravel Backend (API)          │
│  • Authentication & Authorization   │
│  • Business Logic Layer             │
│  • Database Abstraction (Eloquent)  │
└──────────────┬──────────────────────┘
               │
               ↓
┌─────────────────────────────────────┐
│          MySQL Database             │
│  • Clients, Offices, Bookings       │
│  • Invoices, Payments, Documents    │
└─────────────────────────────────────┘
```

#### 📊 Database Schema Highlights

**Main Tables:**
- `users` - Client and admin accounts
- `offices` - Available office spaces
- `bookings` - Reservations and rentals
- `contracts` - Legal agreements
- `invoices` - Billing records
- `payments` - Payment transactions
- `documents` - Uploaded files

#### 🚀 Deployment & Performance

- Deployed on shared/VPS hosting
- Optimized queries for fast response times
- Caching strategy for frequent data
- CDN for static assets
- Backup automation

**Project Link:** *[Link to project folder in this repo]*

---

### 4. 📋 Internship Management System

**Company:** ANEP (Agence Nationale de l'Emploi et des Compétences)  
**Period:** June 2024 - July 2024  
**Role:** Web Developer Intern  
**Project:** Stage Application & Tracking Platform

#### 📋 Project Description

Built a web-based system to **digitize and automate** the internship application process at ANEP. The platform manages the entire workflow from student applications to supervisor assignments and evaluation.

#### 🎯 Key Features

**Student Interface:**
- 📝 Online internship application submission
- 📄 CV and document upload
- 📊 Application status tracking
- 📧 Email notifications for updates
- 📝 Internship report submission
- ⭐ Evaluation feedback viewing

**Administrative Dashboard:**
- 👥 Application review and management
- ✅ Multi-level approval workflow
- 👨‍🏫 Supervisor assignment
- 📊 Statistics and reporting
- 📧 Bulk email notifications
- 🔍 Advanced search and filtering
- 📈 Analytics dashboard

**HR Features:**
- 📋 Application screening
- 🔄 Status updates (Pending, Approved, Rejected)
- 📅 Internship period scheduling
- 📑 Document verification
- 💼 Department assignment
- 🎓 Certificate generation

#### 💻 Technologies Used

**Backend:**
- Django 4.x (Python Framework)
- Django ORM
- Django REST Framework (optional)
- Class-Based Views (CBV)
- Django Authentication System

**Frontend:**
- HTML5 / CSS3
- Bootstrap 5 (Responsive Design)
- JavaScript / jQuery
- Django Template Engine
- AJAX for dynamic updates

**Database:**
- MySQL 8.0
- Relational database design
- Foreign keys and constraints
- Optimized queries

**Additional Tools:**
- Django Admin Interface
- Email backend (SMTP)
- PDF generation (ReportLab)
- File upload handling

#### 🏗️ System Architecture

```
┌─────────────────────────────────────┐
│      Student / HR Portal            │
│  (Django Templates + Bootstrap)     │
└──────────────┬──────────────────────┘
               │ HTTP Requests
               ↓
┌─────────────────────────────────────┐
│         Django Backend              │
│  • URL Routing                      │
│  • Views & Controllers              │
│  • Forms & Validation               │
│  • Business Logic                   │
└──────────────┬──────────────────────┘
               │ Django ORM
               ↓
┌─────────────────────────────────────┐
│          MySQL Database             │
│  • Users, Applications, Supervisors │
│  • Documents, Evaluations, Reports  │
└─────────────────────────────────────┘
```

#### 📊 Database Models

**Core Models:**
```python
- User (Students, HR, Supervisors)
- InternshipApplication
- Department
- Supervisor
- InternshipPeriod
- Document
- Evaluation
- Notification
```

#### 📈 Business Impact

- ⏱️ **80% reduction** in application processing time
- 📄 **100% paperless** application process
- 👥 **500+ applications** managed in first year
- 📊 **Real-time tracking** for all stakeholders
- ✅ **Improved efficiency** in HR operations

**Project Link:** *[Link to project folder in this repo]*

---

## 🛠️ Tech Stack Overview

### Programming Languages

![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQL](https://img.shields.io/badge/SQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

### Frameworks & Libraries

**Backend:**
![Flask](https://img.shields.io/badge/Flask-000000?style=for-the-badge&logo=flask&logoColor=white)
![Django](https://img.shields.io/badge/Django-092E20?style=for-the-badge&logo=django&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)

**Frontend:**
![React Native](https://img.shields.io/badge/React_Native-20232A?style=for-the-badge&logo=react&logoColor=61DAFB)
![Vue.js](https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vue.js&logoColor=4FC08D)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

**AI & ML:**
![PyTorch](https://img.shields.io/badge/PyTorch-EE4C2C?style=for-the-badge&logo=pytorch&logoColor=white)
![TensorFlow](https://img.shields.io/badge/TensorFlow-FF6F00?style=for-the-badge&logo=tensorflow&logoColor=white)
![Transformers](https://img.shields.io/badge/🤗_Transformers-FFD21E?style=for-the-badge)

### Databases

![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Oracle](https://img.shields.io/badge/Oracle-F80000?style=for-the-badge&logo=oracle&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white)

### Tools & DevOps

![Git](https://img.shields.io/badge/Git-F05032?style=for-the-badge&logo=git&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Postman](https://img.shields.io/badge/Postman-FF6C37?style=for-the-badge&logo=postman&logoColor=white)
![VS Code](https://img.shields.io/badge/VS_Code-007ACC?style=for-the-badge&logo=visualstudiocode&logoColor=white)

---

## 💡 Skills Demonstrated

### Technical Skills

#### AI & Machine Learning
- ✅ Large Language Model (LLM) integration
- ✅ Natural Language Processing (NLP)
- ✅ Natural Language to SQL conversion
- ✅ Model deployment and inference optimization
- ✅ CUDA/GPU acceleration
- ✅ Local model deployment for security

#### Full-Stack Development
- ✅ RESTful API design and implementation
- ✅ Single Page Application (SPA) development
- ✅ MVC architecture
- ✅ Database design and optimization
- ✅ Authentication & Authorization
- ✅ Responsive web design

#### Mobile Development
- ✅ Cross-platform app development (React Native)
- ✅ Native API integration
- ✅ Offline-first architecture
- ✅ Real-time geolocation services
- ✅ Push notifications

#### Backend Technologies
- ✅ Python (Flask, Django)
- ✅ PHP (Laravel)
- ✅ Database management (MySQL, Oracle)
- ✅ API development and documentation
- ✅ Server deployment and configuration

#### Frontend Technologies
- ✅ Modern JavaScript (ES6+)
- ✅ Vue.js framework
- ✅ React Native
- ✅ Responsive design (Bootstrap)
- ✅ State management

### Soft Skills

#### Professional Skills
- 🤝 **Team Collaboration:** Worked with cross-functional teams
- 📊 **Project Management:** Managed timelines and deliverables
- 💬 **Communication:** Regular client meetings and presentations
- 📝 **Documentation:** Technical and user documentation
- 🎯 **Problem Solving:** Tackled complex technical challenges

#### Business Acumen
- 💼 **Requirements Analysis:** Gathered and analyzed client needs
- 🎨 **UX/UI Design:** User-centered design approach
- 📈 **Impact Assessment:** Measured business value of solutions
- 🔒 **Security Awareness:** Implemented secure coding practices
- ⚡ **Performance Optimization:** Improved system efficiency

---

## 📂 Repository Structure

```
Internship-Projects/
├── 1_INVOLYS_LLM_ERP_Chatbot/
│   ├── backend/
│   │   ├── app.py                 # Flask application
│   │   ├── models/                # LLM models
│   │   ├── nlp_pipeline/          # NLP processing
│   │   ├── database/              # Oracle connection
│   │   └── requirements.txt
│   ├── docs/
│   │   ├── architecture.md
│   │   ├── api_documentation.md
│   │   └── deployment_guide.md
│   └── README.md
│
├── 2_Mairie_Tanger_Smart_Guide/
│   ├── mobile_app/
│   │   ├── src/
│   │   ├── assets/
│   │   ├── components/
│   │   └── package.json
│   ├── backend/
│   │   ├── llm_service/           # LLM inference
│   │   ├── api/                   # REST API
│   │   └── requirements.txt
│   ├── docs/
│   └── README.md
│
├── 3_Eryx_Domiciliation_Platform/
│   ├── backend/                   # Laravel backend
│   │   ├── app/
│   │   ├── database/
│   │   ├── routes/
│   │   └── composer.json
│   ├── frontend/                  # Vue.js frontend
│   │   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   └── package.json
│   ├── docs/
│   └── README.md
│
└── 4_ANEP_Stage_Management/
    ├── app/                       # Django application
    │   ├── models.py
    │   ├── views.py
    │   ├── forms.py
    │   └── urls.py
    ├── templates/                 # HTML templates
    ├── static/                    # CSS, JS, images
    ├── requirements.txt
    └── README.md
```

---

## 🚀 Getting Started

### Prerequisites

**For Python Projects (INVOLYS, Tanger):**
- Python 3.9+
- CUDA Toolkit (for LLM projects)
- Virtual environment tool (venv, conda)

**For Laravel Project (Eryx):**
- PHP 8.0+
- Composer
- Node.js & NPM
- MySQL

**For Django Project (ANEP):**
- Python 3.8+
- pip
- MySQL

### Installation

#### Clone Repository
```bash
git clone https://github.com/Arib61/Internship-Projects.git
cd Internship-Projects
```

#### Navigate to Specific Project
```bash
# Example: LLM-ERP Chatbot
cd 1_INVOLYS_LLM_ERP_Chatbot/backend
python -m venv venv
source venv/bin/activate  # Linux/Mac
# or
venv\Scripts\activate     # Windows

pip install -r requirements.txt
python app.py
```

#### Each Project Contains:
- 📄 **README.md** - Specific setup instructions
- 📋 **requirements.txt / composer.json** - Dependencies
- 🗂️ **Documentation** - Architecture and API docs
- ⚙️ **Configuration** - Environment setup guides

---

## 📊 Projects Summary

| Project | Company | Duration | Role | Tech Stack | Status |
|---------|---------|----------|------|------------|--------|
| LLM-ERP Chatbot | INVOLYS | 3 months | NLP Engineer | Python, LLM, Flask, Oracle | ✅ Deployed |
| Tanger Smart Guide | Mairie | 3 months | Mobile Dev | React Native, LLM, APIs | ✅ Production |
| Domiciliation Platform | Eryx Maroc | 2 months | Full Stack | Laravel, Vue.js, MySQL | ✅ Complete |
| Stage Management | ANEP | 2 months | Web Dev | Django, Bootstrap, MySQL | ✅ Complete |

---

## 🏆 Key Achievements

### Innovation
- 🤖 **First** to implement local LLM for ERP queries in Morocco
- 📱 **Pioneer** AI-powered tourist guide for Moroccan cities
- 🚀 **Cutting-edge** tech adoption (LLMs, NLP, React Native)

### Business Impact
- 💼 **4 production systems** deployed and used daily
- 👥 **1000+ users** across all platforms
- ⏱️ **70% average** efficiency improvement
- 💰 **Significant cost savings** for organizations

### Technical Excellence
- ✅ **Zero security breaches** in production systems
- ⚡ **High performance** with optimized queries
- 📈 **Scalable architecture** for future growth
- 🔒 **Industry best practices** implementation

---

## 📚 Learning Outcomes

### Technical Growth
1. **AI/ML Production:** Deploying LLMs in real-world applications
2. **Full-Stack Mastery:** End-to-end development capabilities
3. **Enterprise Integration:** Working with legacy systems (Oracle ERP)
4. **Mobile Development:** Cross-platform app creation
5. **DevOps:** Deployment, monitoring, and maintenance

### Professional Development
1. **Client Communication:** Direct interaction with stakeholders
2. **Agile Methodology:** Sprint planning and execution
3. **Code Quality:** Writing maintainable, documented code
4. **Problem Solving:** Debugging complex production issues
5. **Time Management:** Meeting deadlines under pressure

---

## 🎓 Recommendations & References

**All internships resulted in excellent evaluations and recommendation letters.**

> *"Aymane demonstrated exceptional skills in AI/NLP and delivered a production-ready system that exceeded our expectations."*  
> **— INVOLYS, Technical Director**

> *"Outstanding work on the tourist guide app. The local LLM integration was innovative and perfectly executed."*  
> **— Mairie de Tanger, IT Department**

---

## 👨‍💻 Author

**Aymane ARIB**
- 🎓 Final-year Computer Engineering Student at ENSA Tanger
- 💼 Specialized in AI, Machine Learning, NLP & Full-Stack Development
- 🏆 4 successful professional internships completed
- 📧 Email: aribaymane61@gmail.com
- 💼 LinkedIn: [linkedin.com/in/arib-aymane](https://linkedin.com/in/arib-aymane)
- 🐙 GitHub: [@Arib61](https://github.com/Arib61)
- 📍 Location: Rabat, Morocco

---

## 🌟 More Projects

Explore my other repositories:

- 🏆 **[Hackathon Projects](https://github.com/Arib61/Hackathon-Projects)** - Award-winning innovations
- 🤖 **[Machine Learning Projects](https://github.com/Arib61/Machine-Learning-Projects)** - ML portfolio
- 🧠 **[Deep Learning Projects](https://github.com/Arib61/Deep-Learning-Projects)** - DL experiments
- 📚 **[Academic Projects](https://github.com/Arib61/Academic-Projects)** - University work

---

## 📞 Contact & Opportunities

**Open to:**
- 💼 Full-time positions in AI/ML or Full-Stack Development
- 🤝 Freelance projects and collaborations
- 🎓 Research opportunities in NLP/LLMs
- 💡 Consulting on AI integration

**Reach out:**
- 📧 **Email:** aribaymane61@gmail.com
- 💼 **LinkedIn:** [Aymane ARIB](https://linkedin.com/in/arib-aymane)
- 🐙 **GitHub:** [@Arib61](https://github.com/Arib61)
- 📱 **Phone:** +(212) 6 23 89 81 06

---

## 📜 License & Usage

These projects were developed during professional internships. Code samples and documentation are shared for portfolio purposes.

**Note:** Sensitive business logic and proprietary information have been removed or anonymized.

---

<div align="center">

**⭐ If you find these projects impressive, please star this repository!**

**💼 Looking for an AI/ML Engineer or Full-Stack Developer?**  
**Let's connect!**

Made with 💻 and ☕ by [Aymane ARIB](https://github.com/Arib61)

</div>

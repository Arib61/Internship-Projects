# 🤖 Machine Learning Projects Portfolio

<div align="center">

![Machine Learning](https://img.shields.io/badge/Machine-Learning-blue?style=for-the-badge&logo=scikit-learn&logoColor=white)
![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white)
![Data Science](https://img.shields.io/badge/Data-Science-orange?style=for-the-badge&logo=jupyter&logoColor=white)

**Comprehensive collection of 16+ Machine Learning projects covering classification, regression, recommendation systems, and predictive modeling**

[Explore Projects](#-projects) • [Technologies](#-tech-stack) • [Get Started](#-getting-started)

---

[![scikit-learn](https://img.shields.io/badge/scikit--learn-F7931E?style=flat&logo=scikit-learn&logoColor=white)](https://scikit-learn.org/)
[![Pandas](https://img.shields.io/badge/Pandas-150458?style=flat&logo=pandas&logoColor=white)](https://pandas.pydata.org/)
[![NumPy](https://img.shields.io/badge/NumPy-013243?style=flat&logo=numpy&logoColor=white)](https://numpy.org/)
[![Jupyter](https://img.shields.io/badge/Jupyter-F37626?style=flat&logo=jupyter&logoColor=white)](https://jupyter.org/)

</div>

---

## 📖 About This Repository

This repository showcases a diverse collection of **Machine Learning projects** demonstrating proficiency in various ML algorithms, data preprocessing techniques, feature engineering, and model deployment. Each project addresses real-world problems using data-driven approaches and statistical learning methods.

**Portfolio Highlights:**
- 🏥 **Healthcare Analytics:** Disease prediction, medical diagnosis systems
- 💰 **Financial Modeling:** Price predictions, loan status analysis
- 🎬 **Recommendation Systems:** Personalized content suggestions
- 📊 **Business Intelligence:** Sales forecasting, customer insights
- 🎯 **Classification & Regression:** Various supervised learning applications

---

## 🗂️ Projects

### 🏥 Healthcare & Medical Analytics

#### 1. **Diabetes Prediction** (`Diabet/`)
**Objective:** Predict diabetes onset in patients using medical indicators

**Features:**
- Binary classification using patient health metrics
- Feature importance analysis for medical insights
- Model evaluation with precision, recall, and F1-score
- Confusion matrix visualization

**Algorithms:** Logistic Regression, Random Forest, SVM, Gradient Boosting

**Key Metrics:** Glucose levels, BMI, blood pressure, insulin levels

**Deployment:** See `diabetDeployment/` for production-ready model

---

#### 2. **Heart Disease Prediction** (`hearthDisease/`)
**Objective:** Identify patients at risk of heart disease

**Features:**
- Multi-feature medical data analysis
- Risk factor identification
- ROC curve and AUC score analysis
- Cross-validation for robust performance

**Algorithms:** Decision Trees, Random Forest, XGBoost

**Dataset Features:** Age, cholesterol, blood pressure, ECG results

**Impact:** Early detection system for cardiovascular conditions

---

#### 3. **Parkinson's Disease Detection** (`ParkinssonDisease/`)
**Objective:** Detect Parkinson's disease from voice measurements

**Features:**
- Biomedical voice measurement analysis
- Feature engineering for vocal patterns
- High-accuracy classification model
- Clinical application readiness

**Algorithms:** SVM, Random Forest, Ensemble methods

**Dataset:** Voice frequency and amplitude measurements

**Accuracy:** 95%+ classification accuracy

---

#### 4. **Obesity Prediction** (`obesityPrediction/`)
**Objective:** Predict obesity levels based on lifestyle and dietary habits

**Features:**
- Multi-class classification (obesity levels)
- Lifestyle factor analysis
- Dietary habit impact assessment
- Preventive health insights

**Algorithms:** K-Nearest Neighbors, Decision Trees, Neural Networks

**Categories:** Underweight, Normal, Overweight, Obesity Type I/II/III

---

### 💰 Financial & Economic Predictions

#### 5. **House Price Prediction** (`Price House/`)
**Objective:** Predict residential property prices

**Features:**
- Multiple regression analysis
- Feature engineering (location, size, amenities)
- Outlier detection and handling
- Price trend visualization

**Algorithms:** Linear Regression, Ridge, Lasso, XGBoost

**Metrics:** R² Score, MAE, RMSE

**Dataset:** Property specifications, location data, market trends

---

#### 6. **Car Price Estimation** (`CarPrice/`)
**Objective:** Estimate used car prices based on specifications

**Features:**
- Brand, model, year, mileage analysis
- Depreciation modeling
- Market value prediction
- Price comparison tools

**Algorithms:** Multiple Linear Regression, Random Forest Regressor

**Input Features:** Make, model, year, kilometers driven, fuel type

**Use Case:** Used car marketplace pricing

---

#### 7. **Gold Price Prediction** (`GoldPrice/`)
**Objective:** Forecast gold prices using market indicators

**Features:**
- Time series analysis
- Economic indicator correlation
- Market trend prediction
- Investment decision support

**Algorithms:** Time Series Regression, ARIMA, Linear Regression

**Factors:** Stock market indices, currency rates, inflation

---

#### 8. **Loan Status Prediction** (`loanStatus/`)
**Objective:** Predict loan approval likelihood

**Features:**
- Credit risk assessment
- Applicant profile analysis
- Approval/rejection classification
- Risk scoring system

**Algorithms:** Logistic Regression, Decision Trees, Gradient Boosting

**Input:** Income, credit history, loan amount, employment status

**Application:** Banking and financial institutions

---

### 🛒 Business & Retail Analytics

#### 9. **BigMart Sales Prediction** (`bigMart/`)
**Objective:** Forecast product sales for retail stores

**Features:**
- Sales forecasting by product and store
- Inventory optimization insights
- Demand prediction
- Store performance analysis

**Algorithms:** Linear Regression, XGBoost, Random Forest

**Dataset:** Product attributes, store locations, historical sales

**Business Impact:** Inventory management, revenue optimization

---

#### 10. **Movie Recommendation System** (`movieRecomndation/`)
**Objective:** Personalized movie recommendations

**Features:**
- Collaborative filtering
- Content-based filtering
- Hybrid recommendation approach
- User preference learning

**Techniques:** Cosine similarity, Matrix Factorization

**Dataset:** User ratings, movie metadata, genres

**Application:** Streaming platforms, entertainment services

---

#### 11. **Atelier Recommendation System** (`AtelierRecomendation/`)
**Objective:** Workshop/course recommendation engine

**Features:**
- Personalized learning path suggestions
- Skill-based recommendations
- User interest profiling
- Content matching algorithms

**Algorithms:** Collaborative Filtering, Content-Based Filtering

**Use Case:** Educational platforms, skill development

---

### 🎯 Classification & Detection Projects

#### 12. **Spam Email Detection** (`spamEmail/`)
**Objective:** Classify emails as spam or legitimate

**Features:**
- Natural Language Processing (NLP)
- Text preprocessing and cleaning
- TF-IDF vectorization
- High-accuracy spam filtering

**Algorithms:** Naive Bayes, Logistic Regression, SVM

**Dataset:** Email text corpus with spam labels

**Accuracy:** 98%+ spam detection rate

---

#### 13. **Sonar Rock vs Mine Classification** (`sonarRockMine/`)
**Objective:** Distinguish between rocks and underwater mines using sonar

**Features:**
- Signal processing and analysis
- Pattern recognition in sonar data
- Binary classification
- Military/naval applications

**Algorithms:** Neural Networks, SVM, K-Nearest Neighbors

**Dataset:** Sonar frequency responses (60 features)

**Application:** Submarine navigation, underwater detection

---

### 🏃 Health & Fitness

#### 14. **Calorie Burned Prediction** (`calorrieBurned/`)
**Objective:** Estimate calories burned during physical activities

**Features:**
- Activity type and duration analysis
- User profile consideration (age, weight, gender)
- Heart rate integration
- Exercise planning support

**Algorithms:** Linear Regression, Polynomial Regression

**Input Features:** Exercise duration, heart rate, body metrics

**Application:** Fitness apps, health tracking devices

---

### 📁 Additional Resources

#### 15. **General Projects Collection** (`Projects/`)
**Content:** Various exploratory ML projects and experiments

**Includes:**
- Jupyter notebooks with EDA
- Algorithm comparison studies
- Dataset preprocessing examples
- Model evaluation techniques

---

#### 16. **Diabetes Deployment** (`diabetDeployment/`)
**Objective:** Production-ready diabetes prediction model

**Features:**
- Flask/FastAPI web application
- REST API for predictions
- Model serialization (pickle/joblib)
- Deployment-ready code

**Tech Stack:** Flask, scikit-learn, Docker (optional)

**Deployment:** Ready for cloud deployment (AWS, Azure, Heroku)

---

## 🛠️ Tech Stack

### Core Machine Learning Libraries

```python
# Data Manipulation & Analysis
pandas>=1.5.0
numpy>=1.23.0

# Machine Learning
scikit-learn>=1.2.0
xgboost>=1.7.0
lightgbm>=3.3.0

# Visualization
matplotlib>=3.6.0
seaborn>=0.12.0
plotly>=5.11.0

# Model Deployment
flask>=2.3.0
joblib>=1.2.0
pickle

# Jupyter Environment
jupyter>=1.0.0
ipykernel>=6.19.0
```

### Technologies

![Python](https://img.shields.io/badge/Python-3776AB?style=for-the-badge&logo=python&logoColor=white)
![scikit-learn](https://img.shields.io/badge/scikit--learn-F7931E?style=for-the-badge&logo=scikit-learn&logoColor=white)
![Pandas](https://img.shields.io/badge/Pandas-150458?style=for-the-badge&logo=pandas&logoColor=white)
![NumPy](https://img.shields.io/badge/NumPy-013243?style=for-the-badge&logo=numpy&logoColor=white)
![Jupyter](https://img.shields.io/badge/Jupyter-F37626?style=for-the-badge&logo=jupyter&logoColor=white)
![XGBoost](https://img.shields.io/badge/XGBoost-337AB7?style=for-the-badge&logo=xgboost&logoColor=white)
![Flask](https://img.shields.io/badge/Flask-000000?style=for-the-badge&logo=flask&logoColor=white)

---

## 📂 Repository Structure

```
Machine-Learning-Projects/
├── 🏥 Healthcare & Medical
│   ├── Diabet/                    # Diabetes prediction
│   ├── diabetDeployment/          # Deployed diabetes model
│   ├── hearthDisease/             # Heart disease detection
│   ├── ParkinssonDisease/         # Parkinson's detection
│   └── obesityPrediction/         # Obesity level prediction
│
├── 💰 Financial & Economic
│   ├── Price House/               # House price prediction
│   ├── CarPrice/                  # Car price estimation
│   ├── GoldPrice/                 # Gold price forecasting
│   └── loanStatus/                # Loan approval prediction
│
├── 🛒 Business & Retail
│   ├── bigMart/                   # Sales forecasting
│   ├── movieRecomndation/         # Movie recommendations
│   └── AtelierRecomendation/      # Workshop recommendations
│
├── 🎯 Classification & Detection
│   ├── spamEmail/                 # Spam detection
│   ├── sonarRockMine/             # Sonar classification
│   └── calorrieBurned/            # Calorie prediction
│
└── 📁 Projects/                   # General ML experiments
```

---

## 🚀 Getting Started

### Prerequisites

- **Python 3.8+** installed on your system
- **pip** package manager
- **Jupyter Notebook** or **Google Colab**
- Basic understanding of Machine Learning concepts

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/Arib61/Machine-Learning-Projects.git
   cd Machine-Learning-Projects
   ```

2. **Create a virtual environment** (recommended)
   ```bash
   # Windows
   python -m venv venv
   venv\Scripts\activate

   # macOS/Linux
   python3 -m venv venv
   source venv/bin/activate
   ```

3. **Install dependencies**
   ```bash
   pip install pandas numpy scikit-learn matplotlib seaborn jupyter
   # Or if requirements.txt exists:
   pip install -r requirements.txt
   ```

4. **Launch Jupyter Notebook**
   ```bash
   jupyter notebook
   ```

5. **Explore any project**
   - Navigate to a project folder
   - Open the `.ipynb` notebook
   - Run cells sequentially to see results

### Running Individual Projects

```bash
# Example: Run Diabetes Prediction
cd Diabet/
jupyter notebook diabetes_prediction.ipynb

# Example: Test Deployed Model
cd diabetDeployment/
python app.py
# Access at http://localhost:5000
```

---

## 📊 Key ML Concepts Implemented

### 🔄 Data Preprocessing
- ✅ Handling missing values (mean/median imputation)
- ✅ Feature scaling (StandardScaler, MinMaxScaler)
- ✅ Encoding categorical variables (One-Hot, Label Encoding)
- ✅ Outlier detection and treatment (IQR method)
- ✅ Train-test-validation splits

### 🎯 Model Training
- ✅ **Supervised Learning:** Classification & Regression
- ✅ **Unsupervised Learning:** Clustering (in Projects/)
- ✅ **Ensemble Methods:** Random Forest, XGBoost, Voting
- ✅ **Cross-Validation:** K-Fold, Stratified K-Fold
- ✅ **Hyperparameter Tuning:** GridSearchCV, RandomizedSearchCV

### 📈 Model Evaluation
- ✅ **Classification Metrics:** Accuracy, Precision, Recall, F1-Score
- ✅ **Regression Metrics:** R², MAE, RMSE, MAPE
- ✅ **Visualization:** Confusion Matrix, ROC Curve, Learning Curves
- ✅ **Model Comparison:** Performance benchmarking

### 🚀 Deployment
- ✅ Model serialization (Pickle, Joblib)
- ✅ Flask REST API development
- ✅ Web interface creation
- ✅ Production-ready code structure

---

## 🎓 Learning Outcomes

Through these projects, you'll understand:

1. **End-to-End ML Pipeline:** From data collection to deployment
2. **Algorithm Selection:** Choosing the right algorithm for the problem
3. **Feature Engineering:** Creating meaningful features from raw data
4. **Model Optimization:** Improving performance through tuning
5. **Real-World Applications:** Solving practical problems with ML
6. **Best Practices:** Code organization, documentation, reproducibility

---

## 💡 Project Highlights

| Project | Domain | Algorithm | Accuracy/Score | Status |
|---------|--------|-----------|----------------|--------|
| Diabetes Prediction | Healthcare | Random Forest | 95%+ | ✅ Deployed |
| Heart Disease | Healthcare | XGBoost | 92% | ✅ Complete |
| Spam Detection | NLP | Naive Bayes | 98% | ✅ Complete |
| House Price | Finance | XGBoost | R²=0.89 | ✅ Complete |
| Movie Recommendation | Entertainment | Collaborative | - | ✅ Complete |
| BigMart Sales | Retail | XGBoost | RMSE=1200 | ✅ Complete |

---

## 🤝 Contributing

While this is a personal portfolio, suggestions and improvements are welcome!

**To contribute:**
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/improvement`)
3. Commit your changes (`git commit -m 'Add improvement'`)
4. Push to the branch (`git push origin feature/improvement`)
5. Open a Pull Request

---

## 📚 Resources & References

- **scikit-learn Documentation:** https://scikit-learn.org/
- **Kaggle Datasets:** https://www.kaggle.com/datasets
- **Machine Learning Mastery:** https://machinelearningmastery.com/
- **Towards Data Science:** https://towardsdatascience.com/

---

## 👨‍💻 Author

**Aymane ARIB**
- 🎓 Final-year Computer Engineering Student at ENSA Tanger
- 💼 Specialized in AI, Machine Learning & Data Science
- 📧 Email: aribaymane61@gmail.com
- 💼 LinkedIn: [linkedin.com/in/arib-aymane](https://linkedin.com/in/arib-aymane)
- 🐙 GitHub: [@Arib61](https://github.com/Arib61)
- 📍 Location: Rabat, Morocco

---

## 🌟 More Projects

Explore my other repositories:

- 🏆 **[Hackathon Projects](https://github.com/Arib61/Hackathon-Projects)** - Award-winning projects
- 💼 **[Internship Projects](https://github.com/Arib61/Internship-Projects)** - Professional work
- 🧠 **[Deep Learning Projects](https://github.com/Arib61/Deep-Learning-Projects)** - Neural Networks & DL
- 📚 **[Academic Projects](https://github.com/Arib61/Academic-Projects)** - University projects

---

## 📜 License

This project is open source and available for educational purposes. If you use any code, please provide appropriate attribution.

---

## 📞 Contact & Support

Questions? Suggestions? Collaborations?

- 📧 **Email:** aribaymane61@gmail.com
- 💼 **LinkedIn:** [Aymane ARIB](https://linkedin.com/in/arib-aymane)
- 🐙 **GitHub:** [@Arib61](https://github.com/Arib61)

---

<div align="center">

**⭐ If you find these projects helpful, please star this repository!**

Made with ❤️ and ☕ by [Aymane ARIB](https://github.com/Arib61)

**Happy Learning! 🚀**

</div>

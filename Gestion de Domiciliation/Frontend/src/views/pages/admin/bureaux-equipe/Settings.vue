<script setup>
import { ref, onMounted, onBeforeUnmount, computed, watch } from "vue";
import Keyboard from "simple-keyboard";
import "simple-keyboard/build/css/index.css";
import axios from "axios";
import Swal from "sweetalert2";

const form = ref({
  // Informations de la société
  societe_nom: "MAEXPERTISE CONSULTING",
  adresse_siege_social_francais: "44, AVENUE DE FRANCE, 3EME ETAGE, APPRT N° 16, AGDAL-RABAT",
  adresse_siege_social_arabe: "44، شارع فرنسا، الطابق الثالث، الشقة رقم 16، أكدال-الرباط",
  telephone: "0537777604",
  email: "contact@meconsulting.ma",
  website: "https://meconsulting.ma/",
  logo: null,
  icon: null,

  // Informations du président
  president_date_naissance: "2001-01-01",
  president_cin: "AD1234567",
  president_adresse: "44, AVENUE DE FRANCE, 3EME ETAGE, APPRT N° 16, AGDAL-RABAT",
  nom_complet_francais: "MOURAD EL MANSOURI",
  nom_complet_arabe: "مراد المنصوري",

  // Informations légales
  capital_social: "100 000,00",
  ice: "003378206000060",
  rc: "172295",
  identifiant_fiscal: "54001301",
  tp: "25742361",
  type_entreprise: "MORALE",
  type_client: "SARL",
});

// Validation errors
const errors = ref({
  // Informations de la société
  societe_nom: "",
  adresse_siege_social_francais: "",
  adresse_siege_social_arabe: "",
  telephone: "",
  email: "",
  website: "",

  // Informations du président
  president_date_naissance: "",
  president_cin: "",
  president_adresse: "",
  nom_complet_francais: "",
  nom_complet_arabe: "",

  // Informations légales
  capital_social: "",
  ice: "",
  rc: "",
  identifiant_fiscal: "",
  tp: "",
});

const societeId = ref(null);
const isEditMode = ref(false);

// For image previews
const logoPreview = ref(null);
const iconPreview = ref(null);

// Arabic keyboard states
const showArKeyboard = ref(false);
const showAdresseKeyboard = ref(false);
const showPresidentAdresseKeyboard = ref(false);
let keyboardAr = null;
let keyboardAdresse = null;
let keyboardPresidentAdresse = null;

const filteredClientTypes = computed(() => {
  if (form.value.type_entreprise === "MORALE") {
    return ["SARL", "SARL AU", "SA", "SNC", "ASSOCIATION", "COOPERATIVE", "SAS", "SASU", "GIE", "SCP", "SCI"]
  } else if (form.value.type_entreprise === "PHYSIQUE") {
    return ["ENTREPRISE INDIVIDUELLE", "AUTO-ENTREPRENEUR"]
  }
  return []
})

// Validation functions
const validateRequired = (value, fieldName) => {
  if (!value || value.toString().trim() === "") {
    return `${fieldName} est requis`;
  }
  return "";
};

const validateArabicText = (text, fieldName) => {
  if (!text || text.trim() === "") {
    return `${fieldName} est requis`;
  }

  const arabicRegex = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;
  if (!arabicRegex.test(text)) {
    return `${fieldName} doit contenir du texte en arabe`;
  }

  if (text.trim().length < 2) {
    return `${fieldName} doit contenir au moins 2 caractères`;
  }

  return "";
};

const validateEmail = (email) => {
  if (!email || email.trim() === "") {
    return "L'email est requis";
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    return "Format d'email invalide";
  }

  return "";
};

const validatePhone = (phone) => {
  if (!phone || phone.trim() === "") {
    return "Le téléphone est requis";
  }

  const phoneRegex = /^(\+212|0)[5-7][0-9]{8}$|^[0-9]{9,10}$/;
  if (!phoneRegex.test(phone.replace(/[\s-]/g, ''))) {
    return "Format de téléphone invalide (ex: +212 6 12 34 56 78)";
  }

  return "";
};

const validateWebsite = (website) => {
  if (!website || website.trim() === "") {
    return "";
  }

  try {
    new URL(website);
    return "";
  } catch {
    return "Format d'URL invalide (ex: https://www.example.com)";
  }
};

const validateDate = (date, fieldName) => {
  if (!date || date.trim() === "") {
    return `${fieldName} est requise`;
  }

  const dateObj = new Date(date);
  if (isNaN(dateObj.getTime())) {
    return "Format de date invalide";
  }

  const today = new Date();
  const age = today.getFullYear() - dateObj.getFullYear();
  if (age < 18 || age > 100) {
    return "L'âge doit être entre 18 et 100 ans";
  }

  return "";
};

// Fixed formatNumber function for capital social
const formatNumber = (value) => {
  if (!value && value !== 0) return "";

  // Convert to string and remove any existing formatting
  let cleanValue = value.toString().replace(/[^\d.,]/g, '');

  // Handle comma as decimal separator
  cleanValue = cleanValue.replace(',', '.');

  // Parse as float
  const numValue = parseFloat(cleanValue);

  if (isNaN(numValue)) return "";

  // Format with 2 decimal places and French formatting
  return numValue.toLocaleString('fr-FR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

// Function to convert formatted number back to decimal for API
const parseFormattedNumber = (formattedValue) => {
  if (!formattedValue) return 0;
  // Remove spaces and replace comma with dot for parsing
  return parseFloat(formattedValue.toString().replace(/\s/g, '').replace(',', '.')) || 0;
};

const validateCIN = (cin) => {
  if (!cin || cin.trim() === "") {
    return "La CIN est requise";
  }

  // Moroccan CIN format: 1-2 letters followed by 6-7 digits
  const cinRegex = /^[A-Za-z]{1,2}[0-9]{6,7}$/;
  if (!cinRegex.test(cin)) {
    return "Format CIN invalide (ex: AB123456)";
  }

  return "";
};

// Fixed validateCapitalSocial function
const validateCapitalSocial = (value) => {
  if (!value || value.toString().trim() === "") {
    return "Le capital social est requis";
  }

  // Parse the formatted value to get the numeric value
  const numericValue = parseFormattedNumber(value);

  if (isNaN(numericValue) || numericValue <= 0) {
    return "Le capital social doit être un nombre positif";
  }

  if (numericValue < 10000) {
    return "Le capital social minimum est de 10 000,00 DH";
  }

  return "";
};

const validateICE = (ice) => {
  if (!ice || ice.trim() === "") {
    return "L'ICE est requis";
  }
  if (!/^\d{15}$/.test(ice)) {
    return "L'ICE doit contenir exactement 15 chiffres";
  }
  return "";
};

const validateRC = (rc) => {
  if (!rc || rc.trim() === "") {
    return "Le RC est requis";
  }
  if (!/^\d{6,8}$/.test(rc)) {
    return "Le RC doit contenir entre 6 et 8 chiffres";
  }
  return "";
};

const validateIdentifiantFiscal = (if_value) => {
  if (!if_value || if_value.trim() === "") {
    return "L'Identifiant Fiscal est requis";
  }
  if (!/^\d{8}$/.test(if_value)) {
    return "L'Identifiant Fiscal doit contenir exactement 8 chiffres";
  }
  return "";
};

const validateTP = (tp) => {
  if (!tp || tp.trim() === "") {
    return "La Taxe Professionnelle est requise";
  }
  if (!/^\d{8}$/.test(tp)) {
    return "La Taxe Professionnelle doit contenir exactement 8 chiffres";
  }
  return "";
};

// Validate all fields
const validateAllFields = () => {
  // Informations de la société
  errors.value.societe_nom = validateRequired(form.value.societe_nom, "Le nom de la société");
  errors.value.adresse_siege_social_francais = validateRequired(form.value.adresse_siege_social_francais, "L'adresse en français");
  errors.value.adresse_siege_social_arabe = validateArabicText(form.value.adresse_siege_social_arabe, "L'adresse en arabe");
  errors.value.telephone = validatePhone(form.value.telephone);
  errors.value.email = validateEmail(form.value.email);
  errors.value.website = validateWebsite(form.value.website);

  // Informations du président
  errors.value.president_date_naissance = validateDate(form.value.president_date_naissance, "La date de naissance");
  errors.value.president_cin = validateCIN(form.value.president_cin);
  errors.value.president_adresse = validateRequired(form.value.president_adresse, "L'adresse du président");
  errors.value.nom_complet_francais = validateRequired(form.value.nom_complet_francais, "Le nom complet en français");
  errors.value.nom_complet_arabe = validateArabicText(form.value.nom_complet_arabe, "Le nom complet en arabe");

  // Informations légales
  errors.value.capital_social = validateCapitalSocial(form.value.capital_social);
  errors.value.ice = validateICE(form.value.ice);
  errors.value.rc = validateRC(form.value.rc);
  errors.value.identifiant_fiscal = validateIdentifiantFiscal(form.value.identifiant_fiscal);
  errors.value.tp = validateTP(form.value.tp);
};

// Computed property to check if form is valid
const isFormValid = computed(() => {
  return Object.values(errors.value).every(error => error === "");
});

// Add this computed property after the isFormValid computed property
const currentMode = computed(() => {
  return societeId.value ? 'Mise à jour' : 'Création';
});

// Input handlers with validation
const handleInputChange = (field, value) => {
  if (!isEditMode.value) return;

  let processedValue = value;
  let validationResult = "";

  switch (field) {
    case "societe_nom":
      validationResult = validateRequired(value, "Le nom de la société");
      break;
    case "adresse_siege_social_francais":
      validationResult = validateRequired(value, "L'adresse en français");
      break;
    case "adresse_siege_social_arabe":
      validationResult = validateArabicText(value, "L'adresse en arabe");
      break;
    case "telephone":
      validationResult = validatePhone(value);
      break;
    case "email":
      validationResult = validateEmail(value);
      break;
    case "website":
      validationResult = validateWebsite(value);
      break;
    case "president_date_naissance":
      validationResult = validateDate(value, "La date de naissance");
      break;
    case "president_cin":
      processedValue = value.toUpperCase();
      validationResult = validateCIN(processedValue);
      break;
    case "president_adresse":
      validationResult = validateRequired(value, "L'adresse du président");
      break;
    case "nom_complet_francais":
      validationResult = validateRequired(value, "Le nom complet en français");
      break;
    case "nom_complet_arabe":
      validationResult = validateArabicText(value, "Le nom complet en arabe");
      break;
    case "capital_social":
      // Handle capital social formatting
      if (value) {
        // Remove all non-numeric characters except comma and dot
        let cleanValue = value.toString().replace(/[^\d.,]/g, '');

        // Convert comma to dot for parsing
        cleanValue = cleanValue.replace(',', '.');

        // Parse and reformat
        const numValue = parseFloat(cleanValue);
        if (!isNaN(numValue)) {
          processedValue = formatNumber(numValue);
        } else {
          processedValue = "";
        }
      }
      validationResult = validateCapitalSocial(processedValue);
      break;
    case "ice":
      processedValue = value.replace(/\D/g, '');
      if (processedValue.length > 15) processedValue = processedValue.slice(0, 15);
      validationResult = validateICE(processedValue);
      break;
    case "rc":
      processedValue = value.replace(/\D/g, '');
      if (processedValue.length > 8) processedValue = processedValue.slice(0, 8);
      validationResult = validateRC(processedValue);
      break;
    case "identifiant_fiscal":
      processedValue = value.replace(/\D/g, '');
      if (processedValue.length > 8) processedValue = processedValue.slice(0, 8);
      validationResult = validateIdentifiantFiscal(processedValue);
      break;
    case "tp":
      processedValue = value.replace(/\D/g, '');
      if (processedValue.length > 8) processedValue = processedValue.slice(0, 8);
      validationResult = validateTP(processedValue);
      break;
    default:
      break;
  }

  form.value[field] = processedValue;
  errors.value[field] = validationResult;
};

// Function to get image URL from Laravel Storage
function getImageUrl(path) {
  if (!path || path === 'null') return null;
  if (path.startsWith('data:') || path.startsWith('http')) return path;
  return `http://localhost:8000/storage/${path}`;
}

// Fetch société data and determine create/update mode
onMounted(async () => {
  document.addEventListener("mousedown", handleClickOutside);

  try {
    const res = await axios.get("/societes");
    console.log("Société data:", res.data);

    // Check if valid société data exists
    if (res.data && res.data.id) {
      // Update mode - société exists
      societeId.value = res.data.id;
      Object.keys(form.value).forEach((key) => {
        if (res.data[key] !== undefined && key !== "logo" && key !== "icon") {
          form.value[key] = res.data[key];
        }
      });

      // Handle capital social formatting on load
      if (form.value.capital_social) {
        const numValue = parseFormattedNumber(form.value.capital_social);
        form.value.capital_social = formatNumber(numValue);
      }

      // Handle images
      if (res.data.logo && res.data.logo !== "null") {
        logoPreview.value = getImageUrl(res.data.logo);
      } else {
        logoPreview.value = null;
      }

      if (res.data.icon && res.data.icon !== "null") {
        iconPreview.value = getImageUrl(res.data.icon);
      } else {
        iconPreview.value = null;
      }

      validateAllFields();
      console.log("Mode: Update - Société ID:", societeId.value);
    } else {
      // Create mode - no société exists
      societeId.value = null;
      console.log("Mode: Create - No existing société found");
    }
  } catch (e) {
    // Create mode - error fetching data (likely 404 or no société exists)
    societeId.value = null;
    console.log("Mode: Create - Error fetching société data:", e);
  }
});

onBeforeUnmount(() => {
  document.removeEventListener("mousedown", handleClickOutside);
});

function enableEditMode() {
  isEditMode.value = true;
  validateAllFields();
  Swal.fire({
    icon: "info",
    title: "Mode édition activé",
    text: "Vous pouvez maintenant modifier les informations",
    timer: 1500,
    showConfirmButton: false,
    toast: true,
    position: "top-end",
  });
}

function cancelEdit() {
  Swal.fire({
    title: "Annuler les modifications?",
    text: "Toutes les modifications non sauvegardées seront perdues.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Oui, annuler",
    cancelButtonText: "Non, continuer"
  }).then((result) => {
    if (result.isConfirmed) {
      isEditMode.value = false;
      location.reload();
    }
  });
}

function onInput(field, event) {
  if (!isEditMode.value) return;

  const value = event.target.value;
  handleInputChange(field, value);

  if (field === "nom_complet_arabe") {
    if (keyboardAr) keyboardAr.setInput(form.value.nom_complet_arabe);
  } else if (field === "adresse_siege_social_arabe") {
    if (keyboardAdresse) keyboardAdresse.setInput(form.value.adresse_siege_social_arabe);
  } else if (field === "president_adresse") {
    if (keyboardPresidentAdresse) keyboardPresidentAdresse.setInput(form.value.president_adresse);
  }
}

function onFocusKeyboard(type) {
  if (!isEditMode.value) return;

  if (type === "ar") {
    showArKeyboard.value = true;
    if (!keyboardAr) {
      setTimeout(() => {
        keyboardAr = new Keyboard(".keyboard-ar", {
          layout: {
            default: [
              "ض ص ث ق ف غ ع ه خ ح ج د",
              "ش س ي ب ل ا ت ن م ك ط",
              "ئ ء ؤ ر لا ى ة و ز ظ",
              "{space}",
            ],
          },
          display: {
            "{space}": "مسافة",
          },
          rtl: true,
          onChange: (input) => {
            handleInputChange("nom_complet_arabe", input);
          },
          inputName: "nom_complet_arabe",
        });
        keyboardAr.setInput(form.value.nom_complet_arabe);
      }, 0);
    }
  } else if (type === "adresse") {
    showAdresseKeyboard.value = true;
    if (!keyboardAdresse) {
      setTimeout(() => {
        keyboardAdresse = new Keyboard(".keyboard-adresse", {
          layout: {
            default: [
              "ض ص ث ق ف غ ع ه خ ح ج د",
              "ش س ي ب ل ا ت ن م ك ط",
              "ئ ء ؤ ر لا ى ة و ز ظ",
              "{space}",
            ],
          },
          display: {
            "{space}": "مسافة",
          },
          rtl: true,
          onChange: (input) => {
            handleInputChange("adresse_siege_social_arabe", input);
          },
          inputName: "adresse_siege_social_arabe",
        });
        keyboardAdresse.setInput(form.value.adresse_siege_social_arabe);
      }, 0);
    }
  } else if (type === "president_adresse") {
    showPresidentAdresseKeyboard.value = true;
    if (!keyboardPresidentAdresse) {
      setTimeout(() => {
        keyboardPresidentAdresse = new Keyboard(".keyboard-president-adresse", {
          layout: {
            default: [
              "ض ص ث ق ف غ ع ه خ ح ج د",
              "ش س ي ب ل ا ت ن م ك ط",
              "ئ ء ؤ ر لا ى ة و ز ظ",
              "{space}",
            ],
          },
          display: {
            "{space}": "مسافة",
          },
          rtl: true,
          onChange: (input) => {
            handleInputChange("president_adresse", input);
          },
          inputName: "president_adresse",
        });
        keyboardPresidentAdresse.setInput(form.value.president_adresse);
      }, 0);
    }
  }
}

function handleClickOutside(e) {
  const arInput = document.getElementById("nom_complet_arabe");
  const arKeyboard = document.querySelector(".keyboard-ar");
  if (
    showArKeyboard.value &&
    arInput &&
    arKeyboard &&
    !arInput.contains(e.target) &&
    !arKeyboard.contains(e.target)
  ) {
    showArKeyboard.value = false;
  }

  const adresseInput = document.getElementById("adresse_siege_social_arabe");
  const adresseKeyboard = document.querySelector(".keyboard-adresse");
  if (
    showAdresseKeyboard.value &&
    adresseInput &&
    adresseKeyboard &&
    !adresseInput.contains(e.target) &&
    !adresseKeyboard.contains(e.target)
  ) {
    showAdresseKeyboard.value = false;
  }

  const presidentAdresseInput = document.getElementById("president_adresse");
  const presidentAdresseKeyboard = document.querySelector(".keyboard-president-adresse");
  if (
    showPresidentAdresseKeyboard.value &&
    presidentAdresseInput &&
    presidentAdresseKeyboard &&
    !presidentAdresseInput.contains(e.target) &&
    !presidentAdresseKeyboard.contains(e.target)
  ) {
    showPresidentAdresseKeyboard.value = false;
  }
}

function triggerFileInput(inputId) {
  if (!isEditMode.value) {
    Swal.fire({
      icon: "warning",
      title: "Accès refusé",
      text: "Veuillez cliquer sur 'Éditer' pour modifier les informations",
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: "top-end",
    });
    return;
  }
  document.getElementById(inputId).click();
}

function handleFileUpload(event, field) {
  if (!isEditMode.value) return;

  const file = event.target.files[0];
  if (file && file.size <= 5 * 1024 * 1024) {
    form.value[field] = file;

    const reader = new FileReader();
    reader.onload = (e) => {
      if (field === "logo") {
        logoPreview.value = e.target.result;
      } else if (field === "icon") {
        iconPreview.value = e.target.result;
      }
    };
    reader.readAsDataURL(file);
  } else {
    alert("Fichier trop volumineux (max 5MB)");
    event.target.value = null;
  }
}

function removeImage(field) {
  if (!isEditMode.value) return;

  form.value[field] = null;
  if (field === "logo") {
    logoPreview.value = null;
  } else if (field === "icon") {
    iconPreview.value = null;
  }
  const fileInput = document.getElementById(field);
  if (fileInput) fileInput.value = "";
}

async function submitForm() {
  if (!isEditMode.value) {
    Swal.fire({
      icon: "warning",
      title: "Accès refusé",
      text: "Veuillez cliquer sur 'Éditer' pour modifier les informations",
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: "top-end",
    });
    return;
  }

  validateAllFields();

  if (!isFormValid.value) {
    const errorFields = Object.keys(errors.value).filter(key => errors.value[key] !== "");
    Swal.fire({
      icon: "error",
      title: "Erreurs de validation",
      html: `Veuillez corriger les erreurs suivantes:<br><ul style="text-align: left; margin: 10px 0;">${errorFields.map(field => `<li>${errors.value[field]}</li>`).join('')}</ul>`,
      confirmButtonText: "Compris",
    });
    return;
  }

  try {
    const formData = new FormData();
    Object.keys(form.value).forEach((key) => {
      if (form.value[key] !== null && form.value[key] !== undefined) {
        // Convert formatted capital social back to numeric value for API
        if (key === 'capital_social') {
          formData.append(key, parseFormattedNumber(form.value[key]));
        } else {
          formData.append(key, form.value[key]);
        }
      }
    });

    let response;
    let method;

    if (societeId.value) {
      // Update existing société
      response = await axios.post(`/societes/${societeId.value}?_method=PUT`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      method = "update";
    } else {
      // Create new société
      response = await axios.post(`/societes`, formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      method = "create";

      // Set the société ID for future updates
      if (response.data?.id) {
        societeId.value = response.data.id;
      }
    }

    // Update image previews if returned from API
    if (response.data?.logo) {
      logoPreview.value = getImageUrl(response.data.logo);
    }
    if (response.data?.icon) {
      iconPreview.value = getImageUrl(response.data.icon);
    }

    isEditMode.value = false;

    Swal.fire({
      icon: "success",
      title: "Succès!",
      text: method === "create"
        ? "Société créée avec succès!"
        : "Société mise à jour avec succès!",
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: "top-end",
    });
  } catch (e) {
    console.error("Erreur lors de l'envoi du formulaire:", e);
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: "Erreur lors de l'envoi du formulaire. Veuillez réessayer.",
      confirmButtonText: "Compris",
    });
  }
}

function preventMinus(e) {
  if (e.key === '-' || e.key === 'e') e.preventDefault();
}

function checkEditAccess() {
  if (!isEditMode.value) {
    Swal.fire({
      icon: "warning",
      title: "Accès refusé",
      text: "Veuillez cliquer sur 'Éditer' pour modifier les informations",
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: "top-end",
    });
  }
}

watch(() => form.value.type_entreprise, (newVal) => {
  if (!filteredClientTypes.value.includes(form.value.type_client)) {
    form.value.type_client = filteredClientTypes.value[0] || ""
  }
})
</script>

<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content settings-content">
      <div class="page-header settings-pg-header">
        <div class="add-item d-flex">
          <div class="page-title">
            <h4>Paramètres</h4>
            <h6>Gérez les paramètres de votre société</h6>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xl-12">
          <div class="settings-wrapper d-flex">
            <div class="settings-page-wrap">
              <form @submit.prevent="submitForm">
                <div class="setting-title d-flex justify-content-between align-items-center">
                  <h4>Paramètres de la société - {{ currentMode }}</h4>
                  <!-- Edit Mode Indicator -->
                  <div v-if="isEditMode" class="badge bg-success">
                    <i data-feather="edit-3" class="feather-edit-3 me-1"></i>
                    Mode édition activé
                  </div>
                  <div v-else class="badge bg-secondary">
                    <i data-feather="lock" class="feather-lock me-1"></i>
                    Mode lecture seule
                  </div>
                </div>

                <!-- Section: Informations de la société -->
                <div class="card mb-4">
                  <div class="card-header d-flex align-items-center">
                    <i data-feather="building" class="feather-building me-2"></i>
                    <h5 class="mb-0">Informations de la société</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <!-- Nom de la société -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="societe_nom">
                          Nom de la société <span class="text-danger">*</span>
                        </label>
                        <input id="societe_nom" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.societe_nom && isEditMode,
                          'is-valid': !errors.societe_nom && form.societe_nom && isEditMode
                        }" v-model="form.societe_nom" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('societe_nom', e.target.value)" @focus="checkEditAccess"
                          placeholder="Entrez le nom de la société" />
                        <div v-if="errors.societe_nom && isEditMode" class="invalid-feedback">
                          {{ errors.societe_nom }}
                        </div>
                      </div>

                      <!-- Adresse Siège Social Français -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="adresse_siege_social_francais">
                          Adresse Siège Social (FR) <span class="text-danger">*</span>
                        </label>
                        <textarea id="adresse_siege_social_francais" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.adresse_siege_social_francais && isEditMode,
                          'is-valid': !errors.adresse_siege_social_francais && form.adresse_siege_social_francais && isEditMode
                        }" v-model="form.adresse_siege_social_francais" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('adresse_siege_social_francais', e.target.value)"
                          @focus="checkEditAccess" placeholder="Entrez l'adresse en français"></textarea>
                        <div v-if="errors.adresse_siege_social_francais && isEditMode" class="invalid-feedback">
                          {{ errors.adresse_siege_social_francais }}
                        </div>
                      </div>

                      <!-- Adresse Siège Social Arabe -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="adresse_siege_social_arabe">
                          Adresse Siège Social (AR) <span class="text-danger">*</span>
                        </label>
                        <textarea id="adresse_siege_social_arabe" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.adresse_siege_social_arabe && isEditMode,
                          'is-valid': !errors.adresse_siege_social_arabe && form.adresse_siege_social_arabe && isEditMode
                        }" :value="form.adresse_siege_social_arabe" :readonly="!isEditMode"
                          @input="onInput('adresse_siege_social_arabe', $event)"
                          @focus="isEditMode ? onFocusKeyboard('adresse') : checkEditAccess()" style="direction: rtl"
                          placeholder="أدخل العنوان بالعربية"></textarea>
                        <small class="form-text text-muted">Doit contenir du texte en arabe</small>
                        <div v-if="errors.adresse_siege_social_arabe && isEditMode" class="invalid-feedback">
                          {{ errors.adresse_siege_social_arabe }}
                        </div>
                        <div v-show="showAdresseKeyboard && isEditMode" class="keyboard-adresse mt-2"></div>
                      </div>

                      <!-- Téléphone -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="telephone">
                          Téléphone <span class="text-danger">*</span>
                        </label>
                        <input id="telephone" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.telephone && isEditMode,
                          'is-valid': !errors.telephone && form.telephone && isEditMode
                        }" v-model="form.telephone" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('telephone', e.target.value)" @focus="checkEditAccess"
                          placeholder="+212 6 12 34 56 78" />
                        <small class="form-text text-muted">Format: +212 6 12 34 56 78</small>
                        <div v-if="errors.telephone && isEditMode" class="invalid-feedback">
                          {{ errors.telephone }}
                        </div>
                      </div>

                      <!-- Email -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="email">
                          Email <span class="text-danger">*</span>
                        </label>
                        <input id="email" type="email" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.email && isEditMode,
                          'is-valid': !errors.email && form.email && isEditMode
                        }" v-model="form.email" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('email', e.target.value)" @focus="checkEditAccess"
                          placeholder="contact@example.com" />
                        <div v-if="errors.email && isEditMode" class="invalid-feedback">
                          {{ errors.email }}
                        </div>
                      </div>

                      <!-- Website -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="website">Site web</label>
                        <input id="website" type="url" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.website && isEditMode,
                          'is-valid': !errors.website && form.website && isEditMode
                        }" v-model="form.website" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('website', e.target.value)" @focus="checkEditAccess"
                          placeholder="https://www.example.com" />
                        <small class="form-text text-muted">Optionnel</small>
                        <div v-if="errors.website && isEditMode" class="invalid-feedback">
                          {{ errors.website }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section: Informations du président -->
                <div class="card mb-4">
                  <div class="card-header d-flex align-items-center">
                    <i data-feather="user" class="feather-user me-2"></i>
                    <h5 class="mb-0">Informations du président</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <!-- Date de naissance -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="president_date_naissance">
                          Date de naissance <span class="text-danger">*</span>
                        </label>
                        <input id="president_date_naissance" type="date" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.president_date_naissance && isEditMode,
                          'is-valid': !errors.president_date_naissance && form.president_date_naissance && isEditMode
                        }" v-model="form.president_date_naissance" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('president_date_naissance', e.target.value)"
                          @focus="checkEditAccess" />
                        <small class="form-text text-muted">Âge entre 18 et 100 ans</small>
                        <div v-if="errors.president_date_naissance && isEditMode" class="invalid-feedback">
                          {{ errors.president_date_naissance }}
                        </div>
                      </div>

                      <!-- CIN -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="president_cin">
                          CIN <span class="text-danger">*</span>
                        </label>
                        <input id="president_cin" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.president_cin && isEditMode,
                          'is-valid': !errors.president_cin && form.president_cin && isEditMode
                        }" v-model="form.president_cin" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('president_cin', e.target.value)" @focus="checkEditAccess"
                          placeholder="AB123456" maxlength="9" />
                        <small class="form-text text-muted">Format: AB123456</small>
                        <div v-if="errors.president_cin && isEditMode" class="invalid-feedback">
                          {{ errors.president_cin }}
                        </div>
                      </div>

                      <!-- Adresse du président -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="president_adresse">
                          Adresse <span class="text-danger">*</span>
                        </label>
                        <textarea id="president_adresse" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.president_adresse && isEditMode,
                          'is-valid': !errors.president_adresse && form.president_adresse && isEditMode
                        }" v-model="form.president_adresse" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('president_adresse', e.target.value)"
                          @focus="checkEditAccess" placeholder="Adresse du président"></textarea>
                        <div v-if="errors.president_adresse && isEditMode" class="invalid-feedback">
                          {{ errors.president_adresse }}
                        </div>
                      </div>

                      <!-- Nom Complet Français -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="nom_complet_francais">
                          Nom Complet (FR) <span class="text-danger">*</span>
                        </label>
                        <input id="nom_complet_francais" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.nom_complet_francais && isEditMode,
                          'is-valid': !errors.nom_complet_francais && form.nom_complet_francais && isEditMode
                        }" v-model="form.nom_complet_francais" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('nom_complet_francais', e.target.value)"
                          @focus="checkEditAccess" placeholder="Nom complet en français" />
                        <div v-if="errors.nom_complet_francais && isEditMode" class="invalid-feedback">
                          {{ errors.nom_complet_francais }}
                        </div>
                      </div>

                      <!-- Nom Complet Arabe -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="nom_complet_arabe">
                          Nom Complet (AR) <span class="text-danger">*</span>
                        </label>
                        <input id="nom_complet_arabe" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.nom_complet_arabe && isEditMode,
                          'is-valid': !errors.nom_complet_arabe && form.nom_complet_arabe && isEditMode
                        }" :value="form.nom_complet_arabe" :readonly="!isEditMode"
                          @input="onInput('nom_complet_arabe', $event)"
                          @focus="isEditMode ? onFocusKeyboard('ar') : checkEditAccess()" style="direction: rtl"
                          placeholder="الاسم الكامل بالعربية" />
                        <small class="form-text text-muted">Doit contenir du texte en arabe</small>
                        <div v-if="errors.nom_complet_arabe && isEditMode" class="invalid-feedback">
                          {{ errors.nom_complet_arabe }}
                        </div>
                        <div v-show="showArKeyboard && isEditMode" class="keyboard-ar mt-2"></div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section: Images de la société -->
                <div class="card mb-4">
                  <div class="card-header d-flex align-items-center">
                    <i data-feather="image" class="feather-image me-2"></i>
                    <h5 class="mb-0">Images de la société</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <!-- Logo -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="logo">Logo</label>
                        <div class="d-flex align-items-center mb-2">
                          <input id="logo" type="file" class="form-control d-none"
                            @change="handleFileUpload($event, 'logo')" accept="image/*" />
                          <button type="button" class="btn" :class="isEditMode ? 'btn-primary' : 'btn-secondary'"
                            :disabled="!isEditMode" @click="triggerFileInput('logo')">
                            <i data-feather="upload" class="feather-upload me-1"></i>
                            Télécharger
                          </button>
                          <span class="ms-2 text-muted" v-if="form.logo">{{
                            form.logo.name
                          }}</span>
                          <span class="ms-2 text-muted" v-else>Aucun fichier sélectionné</span>
                        </div>
                        <small class="text-muted d-block mb-2">Format recommandé: PNG, JPG. Max 5MB</small>

                        <!-- Logo Preview -->
                        <div v-if="logoPreview" class="mt-2">
                          <div class="border rounded p-2 image-preview-container" style="max-width: 300px">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="fw-bold">Aperçu Logo</span>
                              <button type="button" class="btn btn-sm"
                                :class="isEditMode ? 'btn-danger' : 'btn-secondary'" :disabled="!isEditMode"
                                @click="removeImage('logo')">
                                <i data-feather="trash-2" class="feather-trash-2"></i>
                              </button>
                            </div>
                            <div class="image-preview-wrapper">
                              <img :src="logoPreview" alt="Logo Preview" class="preview-image logo-preview"
                                @error="logoPreview = null" />
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Icon -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="icon">Icône</label>
                        <div class="d-flex align-items-center mb-2">
                          <input id="icon" type="file" class="form-control d-none"
                            @change="handleFileUpload($event, 'icon')" accept="image/*" />
                          <button type="button" class="btn" :class="isEditMode ? 'btn-primary' : 'btn-secondary'"
                            :disabled="!isEditMode" @click="triggerFileInput('icon')">
                            <i data-feather="upload" class="feather-upload me-1"></i>
                            Télécharger
                          </button>
                          <span class="ms-2 text-muted" v-if="form.icon">{{
                            form.icon.name
                          }}</span>
                          <span class="ms-2 text-muted" v-else>Aucun fichier sélectionné</span>
                        </div>
                        <small class="text-muted d-block mb-2">Format recommandé: PNG, ICO. Max 5MB</small>

                        <!-- Icon Preview -->
                        <div v-if="iconPreview" class="mt-2">
                          <div class="border rounded p-2 image-preview-container" style="max-width: 150px">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="fw-bold">Aperçu Icône</span>
                              <button type="button" class="btn btn-sm"
                                :class="isEditMode ? 'btn-danger' : 'btn-secondary'" :disabled="!isEditMode"
                                @click="removeImage('icon')">
                                <i data-feather="trash-2" class="feather-trash-2"></i>
                              </button>
                            </div>
                            <div class="image-preview-wrapper">
                              <img :src="iconPreview" alt="Icon Preview" class="preview-image icon-preview"
                                @error="iconPreview = null" />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Section: Informations légales -->
                <div class="card mb-4">
                  <div class="card-header d-flex align-items-center">
                    <i data-feather="file-text" class="feather-file-text me-2"></i>
                    <h5 class="mb-0">Informations légales</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <!-- Capital Social - Fixed formatting -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="capital_social">
                          Capital Social (MAD) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                          <input id="capital_social" type="text" class="form-control" :class="{
                            'form-control-readonly': !isEditMode,
                            'is-invalid': errors.capital_social && isEditMode,
                            'is-valid': !errors.capital_social && form.capital_social && isEditMode
                          }" v-model="form.capital_social" :readonly="!isEditMode" placeholder="100 000,00"
                            @input="(e) => handleInputChange('capital_social', e.target.value)" @focus="checkEditAccess"
                            autocomplete="off" />
                          <span class="input-group-text">DH</span>
                        </div>
                        <small class="form-text text-muted">Format: 100 000,00 (espaces pour milliers, virgule pour
                          décimales)</small>
                        <div v-if="errors.capital_social && isEditMode" class="invalid-feedback">
                          {{ errors.capital_social }}
                        </div>
                      </div>

                      <!-- ICE -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="ice">
                          ICE <span class="text-danger">*</span>
                        </label>
                        <input id="ice" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.ice && isEditMode,
                          'is-valid': !errors.ice && form.ice && isEditMode
                        }" v-model="form.ice" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('ice', e.target.value)" @focus="checkEditAccess"
                          placeholder="000000000000000" maxlength="15" />
                        <small class="form-text text-muted">15 chiffres exactement</small>
                        <div v-if="errors.ice && isEditMode" class="invalid-feedback">
                          {{ errors.ice }}
                        </div>
                      </div>

                      <!-- RC -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="rc">
                          Registre de Commerce (RC) <span class="text-danger">*</span>
                        </label>
                        <input id="rc" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.rc && isEditMode,
                          'is-valid': !errors.rc && form.rc && isEditMode
                        }" v-model="form.rc" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('rc', e.target.value)" @focus="checkEditAccess"
                          placeholder="000000" maxlength="8" />
                        <small class="form-text text-muted">6 à 8 chiffres</small>
                        <div v-if="errors.rc && isEditMode" class="invalid-feedback">
                          {{ errors.rc }}
                        </div>
                      </div>

                      <!-- Identifiant Fiscal -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="identifiant_fiscal">
                          Identifiant Fiscal <span class="text-danger">*</span>
                        </label>
                        <input id="identifiant_fiscal" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.identifiant_fiscal && isEditMode,
                          'is-valid': !errors.identifiant_fiscal && form.identifiant_fiscal && isEditMode
                        }" v-model="form.identifiant_fiscal" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('identifiant_fiscal', e.target.value)"
                          @focus="checkEditAccess" placeholder="00000000" maxlength="8" />
                        <small class="form-text text-muted">8 chiffres exactement</small>
                        <div v-if="errors.identifiant_fiscal && isEditMode" class="invalid-feedback">
                          {{ errors.identifiant_fiscal }}
                        </div>
                      </div>

                      <!-- TP -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="tp">
                          Taxe Professionnelle (TP) <span class="text-danger">*</span>
                        </label>
                        <input id="tp" type="text" class="form-control" :class="{
                          'form-control-readonly': !isEditMode,
                          'is-invalid': errors.tp && isEditMode,
                          'is-valid': !errors.tp && form.tp && isEditMode
                        }" v-model="form.tp" :readonly="!isEditMode"
                          @input="(e) => handleInputChange('tp', e.target.value)" @focus="checkEditAccess"
                          placeholder="00000000" maxlength="8" />
                        <small class="form-text text-muted">8 chiffres exactement</small>
                        <div v-if="errors.tp && isEditMode" class="invalid-feedback">
                          {{ errors.tp }}
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <label class="form-label">Type d'entreprise</label>
                          <select class="form-select" v-model="form.type_entreprise">
                            <option value="MORALE">Morale</option>
                            <option value="PHYSIQUE">Physique</option>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Type de client</label>
                          <select class="form-select" v-model="form.type_client">
                            <option v-for="type in filteredClientTypes" :key="type" :value="type">
                              {{ type }}
                            </option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-end settings-bottom-btn mt-4">
                  <div class="btn-group">
                    <!-- Edit Button -->
                    <button v-if="!isEditMode" type="button" class="btn btn-warning me-2" @click="enableEditMode">
                      <i data-feather="edit-3" class="feather-edit-3 me-1"></i>
                      Éditer
                    </button>

                    <!-- Cancel Button -->
                    <button v-if="isEditMode" type="button" class="btn btn-secondary me-2" @click="cancelEdit">
                      <i data-feather="x" class="feather-x me-1"></i>
                      Annuler
                    </button>

                    <!-- Save Button -->
                    <button type="submit" class="btn"
                      :class="isEditMode ? (isFormValid ? 'btn-submit' : 'btn-danger') : 'btn-secondary'"
                      :disabled="!isEditMode || !isFormValid">
                      <i data-feather="save" class="feather-save me-1"></i>
                      Enregistrer
                    </button>
                  </div>

                  <!-- Validation Summary -->
                  <div v-if="isEditMode && !isFormValid" class="mt-3">
                    <div class="alert alert-warning">
                      <i data-feather="alert-triangle" class="feather-alert-triangle me-2"></i>
                      <strong>Attention:</strong> Veuillez corriger les erreurs de validation avant de sauvegarder.
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.keyboard-ar,
.keyboard-adresse,
.keyboard-president-adresse {
  z-index: 2000;
  direction: rtl;
}

/* Image preview styling */
.image-preview-container {
  background-color: #f8f9fa;
  border: 2px dashed #dee2e6 !important;
  transition: border-color 0.3s ease;
}

.image-preview-container:hover {
  border-color: #007bff !important;
}

.image-preview-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80px;
}

.preview-image {
  max-width: 100%;
  height: auto;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.logo-preview {
  max-height: 100px;
}

.icon-preview {
  max-height: 64px;
  max-width: 64px;
}

/* Error handling for broken images */
.preview-image[src=""],
.preview-image:not([src]) {
  display: none;
}

/* Validation styling */
.is-invalid {
  border-color: #dc3545;
}

.is-valid {
  border-color: #28a745;
}

.invalid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #dc3545;
}

.valid-feedback {
  display: block;
  width: 100%;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #28a745;
}

.text-danger {
  color: #dc3545 !important;
}

.form-text {
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #6c757d;
}

.alert {
  padding: 0.75rem 1.25rem;
  margin-bottom: 1rem;
  border: 1px solid transparent;
  border-radius: 0.25rem;
}

.alert-warning {
  color: #856404;
  background-color: #fff3cd;
  border-color: #ffeaa7;
}

/* Card styling */
.card {
  border: 1px solid #e0e0e0;
  border-radius: 0.25rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.card-header {
  background-color: #f8f9fa;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e0e0e0;
  border-radius: 0.25rem 0.25rem 0 0;
}

.card-body {
  padding: 1rem;
}

/* Section icons */
.feather-building {
  color: #007bff;
}

.feather-user {
  color: #28a745;
}

.feather-image {
  color: #fd7e14;
}

.feather-file-text {
  color: #6f42c1;
}

/* Utility classes */
.me-1 {
  margin-right: 0.25rem;
}

.me-2 {
  margin-right: 0.5rem;
}

.mb-0 {
  margin-bottom: 0;
}

.mb-2 {
  margin-bottom: 0.5rem;
}

.mt-2 {
  margin-top: 0.5rem;
}

.mt-3 {
  margin-top: 1rem;
}

.ms-2 {
  margin-left: 0.5rem;
}

.d-none {
  display: none;
}

.d-block {
  display: block;
}

.d-flex {
  display: flex;
}

.align-items-center {
  align-items: center;
}

.justify-content-between {
  justify-content: space-between;
}

.text-muted {
  color: #6c757d;
}

.fw-bold {
  font-weight: 700;
}

.border {
  border: 1px solid #dee2e6;
}

.rounded {
  border-radius: 0.25rem;
}

.p-2 {
  padding: 0.5rem;
}

/* Button styling */
.btn-primary {
  color: #fff;
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-danger {
  color: #fff;
  background-color: #dc3545;
  border-color: #dc3545;
}

.btn-warning {
  color: #000;
  background-color: #ffc107;
  border-color: #ffc107;
}

.btn-secondary {
  color: #fff;
  background-color: #6c757d;
  border-color: #6c757d;
}

.btn-submit {
  color: #fff;
  background-color: #198754;
  border-color: #198754;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  border-radius: 0.2rem;
}

/* Form control readonly styling */
.form-control-readonly {
  background-color: #f8f9fa;
  opacity: 1;
}

/* Badge styling */
.badge {
  display: inline-block;
  padding: 0.35em 0.65em;
  font-size: 0.75em;
  font-weight: 700;
  line-height: 1;
  color: #fff;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 0.25rem;
}

.bg-success {
  background-color: #198754;
}

.bg-secondary {
  background-color: #6c757d;
}

/* //ddw */
</style>
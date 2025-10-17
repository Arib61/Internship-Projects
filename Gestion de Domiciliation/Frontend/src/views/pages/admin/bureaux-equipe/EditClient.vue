<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import Keyboard from "simple-keyboard";
import "simple-keyboard/build/css/index.css";
import axios from "axios";
import Swal from "sweetalert2"; 
import tribunal from "@/assets/json/tribunal.json"
import type_tribunal from "@/assets/json/type_tribunal.json"
import countries from "@/assets/json/countries.json"
import cities from "@/assets/json/cities.json"

const route = useRoute();
const router = useRouter();
const clientId = route.params.id;

const isEditMode = ref(false);
const loading = ref(true);

// Variables réactives pour tribunaux
const tribunauxPrimaires = ref([])
const tribunauxCommerciaux = ref([])
const typesTribunal = ref([])

// Variables réactives pour countries et cities
const paysList = ref([])
const villesList = ref([])

// Variables pour gérer les fichiers existants
const existingCin = ref(null);
const existingCertificat = ref(null);

const form = ref({
  nom_francais: "",
  nom_arabe: "",
  adresse_siege_social_francais: "",
  adresse_siege_social_arabe: "",
  capital_social: "",
  ice: "",
  cin: null,
  certificat_negative: null,
  rc: "",
  identifiant_fiscal: "",
  tp: "",
  tribunal: "",
  type_tribunal: "",
  telephone: "",
  email: "",
  pays: "",
  ville: "",
  website: "",
  type_entreprise: "MORALE",
  type_client: "SARL",
  status: "PROSPECT",
  gerant_id: null,
  gestionnaire_id: null
});

// Validation errors
const errors = ref({
  nom_francais: "",
  nom_arabe: "",
  adresse_siege_social_francais: "",
  adresse_siege_social_arabe: "",
  capital_social: "",
  ice: "",
  rc: "",
  identifiant_fiscal: "",
  tp: "",
  telephone: "",
  email: "",
  website: "",
});

// Pour le formulaire de gérant
const gerantForm = ref({
  nom: "",
  cin: "",
  address: "",
  date_naissance: "",
  telephone: "",
  email: ""
});

// Liste des gérants
const gerants = ref([]);
const showGerantModal = ref(false);
const isLoadingGerants = ref(false);
const gerantModalMode = ref('add');

const cinPreview = ref(null);
const certificatPreview = ref(null);

const showArKeyboard = ref(false);
const showAdresseKeyboard = ref(false);
let keyboardAr = null;
let keyboardAdresse = null;

// Computed property pour filtrer les cities selon le pays sélectionné
const villesFiltered = computed(() => {
  if (form.value.pays) {
    const selectedCountry = paysList.value.find((country) => country.name === form.value.pays)
    if (selectedCountry) {
      return villesList.value.filter((ville) => ville.country_id === selectedCountry.id)
    }
  }
  return villesList.value
})

// Filtrer les types de client selon le type d'entreprise
const filteredClientTypes = computed(() => {
  if (form.value.type_entreprise === "MORALE") {
    return ["SARL", "SARL AU", "SA", "SNC", "ASSOCIATION", "COOPERATIVE", "SAS", "SASU", "GIE", "SCP", "SCI"]
  } else if (form.value.type_entreprise === "PHYSIQUE") {
    return ["ENTREPRISE INDIVIDUELLE", "AUTO-ENTREPRENEUR"]
  }
  return []
})

// Computed property pour filtrer les tribunaux
const tribunauxFiltered = computed(() => {
  if (form.value.type_tribunal === "المحكمة الابتدائية") {
    return tribunauxPrimaires.value
  } else if (form.value.type_tribunal === "المحكمة التجارية") {
    return tribunauxCommerciaux.value
  } else {
    return []
  }
})

// Fonction pour initialiser toutes les données
function initializeTribunalData() {
  typesTribunal.value = type_tribunal
  tribunauxPrimaires.value = tribunal.filter((t) => t.id_type === 2)
  tribunauxCommerciaux.value = tribunal.filter((t) => t.id_type === 1)
  paysList.value = countries
  villesList.value = cities
}

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

const validateCapitalSocial = (capital) => {
  if (!capital || capital.toString().trim() === "") {
    return "Le capital social est requis";
  }
  
  const numValue = parseFloat(capital);
  if (isNaN(numValue) || numValue < 0) {
    return "Le capital social doit être un nombre positif";
  }
  
  return "";
};

const validateICE = (ice) => {
  if (!ice || ice.trim() === "") {
    return "L'ICE est requis";
  }
  const cleanIce = ice.replace(/\D/g, '');
  if (cleanIce.length !== 15) {
    return "L'ICE doit contenir exactement 15 chiffres";
  }
  return "";
};

const validateRC = (rc) => {
  if (!rc || rc.trim() === "") {
    return "Le RC est requis";
  }
  const cleanRC = rc.replace(/\D/g, '');
  if (cleanRC.length < 6 || cleanRC.length > 8) {
    return "Le RC doit contenir entre 6 et 8 chiffres";
  }
  return "";
};

const validateIdentifiantFiscal = (if_value) => {
  if (!if_value || if_value.trim() === "") {
    return "L'Identifiant Fiscal est requis";
  }
  const cleanIF = if_value.replace(/\D/g, '');
  if (cleanIF.length !== 8) {
    return "L'Identifiant Fiscal doit contenir exactement 8 chiffres";
  }
  return "";
};

const validateTP = (tp) => {
  if (!tp || tp.trim() === "") {
    return "La Taxe Professionnelle est requise";
  }
  const cleanTP = tp.replace(/\D/g, '');
  if (cleanTP.length !== 8) {
    return "La Taxe Professionnelle doit contenir exactement 8 chiffres";
  }
  return "";
};

// Validate all fields - Les fichiers ne sont plus requis en modification
const validateAllFields = () => {
  errors.value.nom_francais = validateRequired(form.value.nom_francais, "Le nom français");
  errors.value.nom_arabe = validateArabicText(form.value.nom_arabe, "Le nom arabe");
  errors.value.adresse_siege_social_francais = validateRequired(form.value.adresse_siege_social_francais, "L'adresse en français");
  errors.value.adresse_siege_social_arabe = validateArabicText(form.value.adresse_siege_social_arabe, "L'adresse en arabe");
  errors.value.capital_social = validateCapitalSocial(form.value.capital_social);
  errors.value.ice = validateICE(form.value.ice);
  errors.value.rc = validateRC(form.value.rc);
  errors.value.identifiant_fiscal = validateIdentifiantFiscal(form.value.identifiant_fiscal);
  errors.value.tp = validateTP(form.value.tp);
  errors.value.telephone = validatePhone(form.value.telephone);
  errors.value.email = validateEmail(form.value.email);
  
  // Les fichiers ne sont plus requis en modification
  // Ils gardent leurs valeurs précédentes si pas modifiés
};

// Computed property to check if form is valid
const isFormValid = computed(() => {
  return Object.values(errors.value).every(error => error === "");
});

// Function to get image URL from Laravel Storage
function getImageUrl(path) {
  if (!path || path === 'null') return null;
  if (path.startsWith('data:') || path.startsWith('http')) return path;
  return `http://localhost:8000/storage/${path}`;
}

// Récupérer la liste des gérants
async function fetchGerants() {
  try {
    isLoadingGerants.value = true;
    const response = await axios.get("/gerants");
    gerants.value = response.data;
  } catch (error) {
    console.error("Erreur lors de la récupération des gérants:", error);
  } finally {
    isLoadingGerants.value = false;
  }
}

// Fetch client data and fill form
async function fetchClientData() {
  loading.value = true;
  
  try {
    const response = await axios.get(`/clients/${clientId}`);
    const clientData = response.data;
    
    // Fill form with client data
    Object.keys(form.value).forEach((key) => {
      if (clientData[key] !== undefined && key !== "cin" && key !== "certificat_negative") {
        form.value[key] = clientData[key];
      }
    });
    
    // Gérer les fichiers existants - les conserver comme référence
    if (clientData.cin && clientData.cin !== "null") {
      existingCin.value = clientData.cin;
      cinPreview.value = getImageUrl(clientData.cin);
    }
    
    if (clientData.certificat_negative && clientData.certificat_negative !== "null") {
      existingCertificat.value = clientData.certificat_negative;
      certificatPreview.value = getImageUrl(clientData.certificat_negative);
    }
    
    // Validate loaded data
    validateAllFields();
    
    document.title = `Modifier: ${clientData.nom_francais || 'Client'}`;
  } catch (error) {
    console.error("Erreur lors du chargement des données client:", error);
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: "Impossible de charger les informations du client.",
      confirmButtonText: "Retour à la liste",
    }).then(() => {
      router.push('/admin/clients');
    });
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  document.addEventListener("mousedown", handleClickOutside);
  initializeTribunalData();
  await fetchGerants();
  await fetchClientData();
});

onBeforeUnmount(() => {
  document.removeEventListener("mousedown", handleClickOutside);
});

// Fonction pour gérer le changement de pays
function handlePaysChange() {
  form.value.ville = "";
}

// Fonction pour gérer le changement de type d'entreprise
function handleTypeEntrepriseChange() {
  // Réinitialiser le type de client si il n'est pas compatible
  if (!filteredClientTypes.value.includes(form.value.type_client)) {
    form.value.type_client = filteredClientTypes.value[0] || "";
  }
}

function handleTypeTribunalChange() {
  form.value.tribunal = "";
}

function enableEditMode() {
  isEditMode.value = true;
  validateAllFields();
  
  fetchGerants();
  
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
      router.push(`/admin/clients/${clientId}`);
    }
  });
}

// Input handlers with validation
const handleNomFrancaisInput = (event) => {
  if (!isEditMode.value) return;
  form.value.nom_francais = event.target.value;
  errors.value.nom_francais = validateRequired(form.value.nom_francais, "Le nom français");
};

const handleNomArabeInput = (value) => {
  if (!isEditMode.value) return;
  form.value.nom_arabe = value;
  errors.value.nom_arabe = validateArabicText(form.value.nom_arabe, "Le nom arabe");
};

const handleCapitalSocialInput = (event) => {
  if (!isEditMode.value) return;
  
  let value = event.target.value.replace(/^0+(?=\d)/, '');
  value = value.replace(/[^0-9.]/g, '');
  value = value.replace(/(\..*)\./g, '$1');
  
  form.value.capital_social = value;
  errors.value.capital_social = validateCapitalSocial(form.value.capital_social);
};

const handleICEInput = (event) => {
  if (!isEditMode.value) return;
  
  let value = event.target.value.replace(/\D/g, '');
  
  if (value.length > 15) {
    value = value.substring(0, 15);
  }
  
  form.value.ice = value;
  errors.value.ice = validateICE(form.value.ice);
};

const handleIdentifiantFiscalInput = (event) => {
  if (!isEditMode.value) return;
  
  let value = event.target.value.replace(/\D/g, '');
  
  if (value.length > 8) {
    value = value.substring(0, 8);
  }
  
  form.value.identifiant_fiscal = value;
  errors.value.identifiant_fiscal = validateIdentifiantFiscal(form.value.identifiant_fiscal);
};

const handleTPInput = (event) => {
  if (!isEditMode.value) return;
  
  let value = event.target.value.replace(/\D/g, '');
  
  if (value.length > 8) {
    value = value.substring(0, 8);
  }
  
  form.value.tp = value;
  errors.value.tp = validateTP(form.value.tp);
};

const handleRCInput = (event) => {
  if (!isEditMode.value) return;
  
  let value = event.target.value.replace(/\D/g, '');
  
  if (value.length > 8) {
    value = value.substring(0, 8);
  }
  
  form.value.rc = value;
  errors.value.rc = validateRC(form.value.rc);
};

const handleTelephoneInput = (event) => {
  if (!isEditMode.value) return;
  form.value.telephone = event.target.value;
  errors.value.telephone = validatePhone(form.value.telephone);
};

const handleEmailInput = (event) => {
  if (!isEditMode.value) return;
  form.value.email = event.target.value;
  errors.value.email = validateEmail(form.value.email);
};

const handleAdresseFrancaisInput = (event) => {
  if (!isEditMode.value) return;
  form.value.adresse_siege_social_francais = event.target.value;
  errors.value.adresse_siege_social_francais = validateRequired(form.value.adresse_siege_social_francais, "L'adresse en français");
};

function onInput(field, event) {
  if (!isEditMode.value) return;
  
  const value = event.target.value;
  
  if (field === "nom_arabe") {
    handleNomArabeInput(value);
    if (keyboardAr) keyboardAr.setInput(form.value.nom_arabe);
  } else if (field === "adresse_siege_social_arabe") {
    form.value.adresse_siege_social_arabe = value;
    errors.value.adresse_siege_social_arabe = validateArabicText(value, "L'adresse en arabe");
    if (keyboardAdresse) keyboardAdresse.setInput(form.value.adresse_siege_social_arabe);
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
            handleNomArabeInput(input);
          },
          inputName: "nom_arabe",
        });
        keyboardAr.setInput(form.value.nom_arabe);
      }, 0);
    }
  }
  if (type === "adresse") {
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
            form.value.adresse_siege_social_arabe = input;
            errors.value.adresse_siege_social_arabe = validateArabicText(input, "L'adresse en arabe");
          },
          inputName: "adresse_siege_social_arabe",
        });
        keyboardAdresse.setInput(form.value.adresse_siege_social_arabe);
      }, 0);
    }
  }
}

function handleClickOutside(e) {
  const arInput = document.getElementById("nom_arabe");
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

  const gerantModal = document.querySelector(".gerant-modal");
  const gerantModalContent = document.querySelector(".gerant-modal-content");
  if (
    showGerantModal.value &&
    gerantModal &&
    gerantModalContent &&
    !gerantModalContent.contains(e.target) &&
    e.target.className !== "add-gerant-btn" &&
    !e.target.closest(".add-gerant-btn")
  ) {
    showGerantModal.value = false;
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
      if (field === "cin") {
        cinPreview.value = e.target.result;
      } else if (field === "certificat_negative") {
        certificatPreview.value = e.target.result;
      }
    };
    reader.readAsDataURL(file);
  } else {
    Swal.fire({
      icon: "warning",
      title: "Attention",
      text: "Fichier trop volumineux (max 5MB)",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
    });
    event.target.value = null;
  }
}

function removeFile(field) {
  if (!isEditMode.value) return;
  
  // Confirmer la suppression
  Swal.fire({
    title: "Supprimer le fichier?",
    text: "Êtes-vous sûr de vouloir supprimer ce fichier?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Oui, supprimer",
    cancelButtonText: "Annuler"
  }).then((result) => {
    if (result.isConfirmed) {
      form.value[field] = null;
      if (field === "cin") {
        cinPreview.value = null;
        existingCin.value = null;
      } else if (field === "certificat_negative") {
        certificatPreview.value = null;
        existingCertificat.value = null;
      }
      const fileInput = document.getElementById(field);
      if (fileInput) fileInput.value = "";
      
      Swal.fire({
        icon: "success",
        title: "Fichier supprimé",
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 2000,
      });
    }
  });
}

function handleGerantChange(event) {
  const value = event.target.value;
  
  if (value === "add_new") {
    event.target.value = "";
    form.value.gerant_id = null;
    openAddGerantModal();
  } else {
    form.value.gerant_id = value;
  }
}

function openAddGerantModal() {
  gerantForm.value = {
    nom: "",
    cin: "",
    address: "",
    date_naissance: "",
    telephone: "",
    email: ""
  };
  gerantModalMode.value = 'add';
  showGerantModal.value = true;
  form.value.gerant_id = null;
}

function closeGerantModal() {
  showGerantModal.value = false;
}

async function submitGerantForm() {
  try {
    const response = await axios.post("/gerants", gerantForm.value);
    const newGerant = response.data.data || response.data;

    gerants.value.push(newGerant);
    form.value.gerant_id = newGerant.id;
    
    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Gérant ajouté avec succès",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 2000,
    });
    
    closeGerantModal();
  } catch (error) {
    console.error("Erreur lors de l'ajout du gérant:", error);
    
    let errorMessage = "Impossible d'ajouter le gérant";
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: errorMessage,
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
    });
  }
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
    
    // Ajouter tous les champs sauf les fichiers
    Object.keys(form.value).forEach((key) => {
      if (key !== "cin" && key !== "certificat_negative" && form.value[key] !== null && form.value[key] !== undefined) {
        formData.append(key, form.value[key]);
      }
    });

    // Gérer les fichiers : seulement si nouveaux fichiers sélectionnés
    if (form.value.cin instanceof File) {
      formData.append('cin', form.value.cin);
    }
    
    if (form.value.certificat_negative instanceof File) {
      formData.append('certificat_negative', form.value.certificat_negative);
    }

    const response = await axios.post(`/clients/${clientId}?_method=PUT`, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    isEditMode.value = false;

    Swal.fire({
      icon: "success",
      title: "Succès!",
      text: "Client mis à jour avec succès!",
      timer: 2000,
      showConfirmButton: false,
      toast: true,
      position: "top-end",
    });

    setTimeout(() => {
      router.push(`/admin/clients/${clientId}`);
    }, 2000);

  } catch (error) {
    console.error("Erreur lors de la mise à jour:", error);
    
    let errorMessage = "Erreur lors de la mise à jour du client.";
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message;
    }
    
    Swal.fire({
      icon: "error",
      title: "Erreur!",
      text: errorMessage,
      showConfirmButton: true,
      confirmButtonText: "OK",
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
</script>

<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content settings-content">
      <!-- Loading state -->
      <div v-if="loading" class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement...</span>
        </div>
        <p class="mt-3">Chargement des informations du client...</p>
      </div>

      <!-- Main content -->
      <div v-else>
        <div class="page-header settings-pg-header">
          <div class="d-flex align-items-start">
            <button 
              type="button"
              class="btn btn-back-custom me-3"
              @click="router.push(`/admin/clients/${clientId}`)"
              data-bs-toggle="tooltip" 
              data-bs-placement="top" 
              title="Retour aux détails du client"
            >
              <i data-feather="arrow-left" class="feather-arrow-left me-2"></i>
              Retour
            </button>
            
            <div class="page-title">
              <h4 class="client-edit-title">Modifier le client</h4>
              <h6>{{ form.nom_francais }}</h6>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-12">
            <div class="settings-wrapper d-flex">
              <div class="settings-page-wrap">
                <form @submit.prevent="submitForm">
                  <div class="setting-title d-flex justify-content-between align-items-center">
                    <h4>Informations du client</h4>
                    <div v-if="isEditMode" class="badge bg-success">
                      <i data-feather="edit-3" class="feather-edit-3 me-1"></i>
                      Mode édition activé
                    </div>
                    <div v-else class="badge bg-secondary">
                      <i data-feather="lock" class="feather-lock me-1"></i>
                      Mode lecture seule
                    </div>
                  </div>

                  <!-- Section: Informations générales -->
                  <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                      <i data-feather="info" class="feather-info me-2"></i>
                      <h5 class="mb-0">Informations générales</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Type d'entreprise</label>
                          <select 
                            class="form-select" 
                            v-model="form.type_entreprise"
                            :disabled="!isEditMode"
                            :class="{ 'form-control-readonly': !isEditMode }"
                            @change="handleTypeEntrepriseChange"
                          >
                            <option value="MORALE">Morale</option>
                            <option value="PHYSIQUE">Physique</option>
                          </select>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Type de client</label>
                          <select 
                            class="form-select" 
                            v-model="form.type_client"
                            :disabled="!isEditMode"
                            :class="{ 'form-control-readonly': !isEditMode }"
                          >
                            <option v-for="type in filteredClientTypes" :key="type" :value="type">
                              {{ type }}
                            </option>
                          </select>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Statut</label>
                          <select 
                            class="form-select" 
                            v-model="form.status"
                            :disabled="!isEditMode"
                            :class="{ 'form-control-readonly': !isEditMode }"
                          >
                            <option value="PROSPECT">Prospect</option>
                            <option value="ACTIVE">Actif</option>
                            <option value="INACTIVE">Inactif</option>
                          </select>
                        </div>
                        
                        <!-- Nom Français -->
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="nom_francais">
                            Nom Français <span class="text-danger">*</span>
                          </label>
                          <input
                            id="nom_francais"
                            type="text"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.nom_francais && isEditMode,
                              'is-valid': !errors.nom_francais && form.nom_francais && isEditMode
                            }"
                            v-model="form.nom_francais"
                            :readonly="!isEditMode"
                            @input="handleNomFrancaisInput"
                            @focus="checkEditAccess"
                            placeholder="Entrez le nom en français"
                          />
                          <div v-if="errors.nom_francais && isEditMode" class="invalid-feedback">
                            {{ errors.nom_francais }}
                          </div>
                        </div>
                        
                        <!-- Nom Arabe -->
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="nom_arabe">
                            Nom Arabe <span class="text-danger">*</span>
                          </label>
                          <input
                            id="nom_arabe"
                            type="text"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.nom_arabe && isEditMode,
                              'is-valid': !errors.nom_arabe && form.nom_arabe && isEditMode
                            }"
                            :value="form.nom_arabe"
                            :readonly="!isEditMode"
                            @input="onInput('nom_arabe', $event)"
                            @focus="isEditMode ? onFocusKeyboard('ar') : checkEditAccess()"
                            style="direction: rtl"
                            placeholder="أدخل الاسم بالعربية"
                          />
                          <div v-if="errors.nom_arabe && isEditMode" class="invalid-feedback">
                            {{ errors.nom_arabe }}
                          </div>
                          <div v-show="showArKeyboard && isEditMode" class="keyboard-ar mt-2"></div>
                        </div>
                        
                        <!-- Gérant -->
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="gerant_id">Gérant</label>
                          <div class="input-group">
                            <select 
                              id="gerant_id" 
                              class="form-select" 
                              v-model="form.gerant_id"
                              @change="handleGerantChange"
                              :disabled="!isEditMode"
                              :class="{ 'form-control-readonly': !isEditMode }"
                            >
                              <option value="" disabled>Sélectionnez un gérant</option>
                              <option v-for="gerant in gerants" :key="gerant.id" :value="gerant.id">
                                {{ gerant.nom }} 
                              </option>
                              <option v-if="isEditMode" value="add_new" class="text-primary fw-bold">+ Ajouter un nouveau gérant</option>
                            </select>
                            <button 
                              type="button" 
                              class="btn btn-outline-primary add-gerant-btn"
                              @click="openAddGerantModal"
                              :disabled="!isEditMode"
                            >
                              <i data-feather="plus" class="feather-plus"></i>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Section: Adresse -->
                  <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                      <i data-feather="map-pin" class="feather-map-pin me-2"></i>
                      <h5 class="mb-0">Adresse</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="adresse_siege_social_francais">
                            Adresse Siège Social (FR) <span class="text-danger">*</span>
                          </label>
                          <textarea
                            id="adresse_siege_social_francais"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.adresse_siege_social_francais && isEditMode,
                              'is-valid': !errors.adresse_siege_social_francais && form.adresse_siege_social_francais && isEditMode
                            }"
                            v-model="form.adresse_siege_social_francais"
                            :readonly="!isEditMode"
                            @input="handleAdresseFrancaisInput"
                            @focus="checkEditAccess"
                            placeholder="Entrez l'adresse en français"
                          ></textarea>
                          <div v-if="errors.adresse_siege_social_francais && isEditMode" class="invalid-feedback">
                            {{ errors.adresse_siege_social_francais }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="adresse_siege_social_arabe">
                            Adresse Siège Social (AR) <span class="text-danger">*</span>
                          </label>
                          <textarea
                            id="adresse_siege_social_arabe"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.adresse_siege_social_arabe && isEditMode,
                              'is-valid': !errors.adresse_siege_social_arabe && form.adresse_siege_social_arabe && isEditMode
                            }"
                            :value="form.adresse_siege_social_arabe"
                            :readonly="!isEditMode"
                            @input="onInput('adresse_siege_social_arabe', $event)"
                            @focus="isEditMode ? onFocusKeyboard('adresse') : checkEditAccess()"
                            style="direction: rtl"
                            placeholder="أدخل العنوان بالعربية"
                          ></textarea>
                          <div v-if="errors.adresse_siege_social_arabe && isEditMode" class="invalid-feedback">
                            {{ errors.adresse_siege_social_arabe }}
                          </div>
                          <div v-show="showAdresseKeyboard && isEditMode" class="keyboard-adresse mt-2"></div>
                        </div>
                        
                        <!-- Pays (Select) -->
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="pays">Pays</label>
                          <select 
                            id="pays" 
                            class="form-select" 
                            v-model="form.pays" 
                            @change="handlePaysChange"
                            :disabled="!isEditMode"
                            :class="{ 'form-control-readonly': !isEditMode }"
                          >
                            <option value="">Sélectionnez un pays</option>
                            <option v-for="paysItem in paysList" :key="paysItem.id" :value="paysItem.name">
                              {{ paysItem.name }}
                            </option>
                          </select>
                        </div>
                        
                        <!-- Ville (Select) -->
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="ville">Ville</label>
                          <select 
                            id="ville" 
                            class="form-select" 
                            v-model="form.ville" 
                            :disabled="!form.pays || !isEditMode"
                            :class="{ 'form-control-readonly': !isEditMode }"
                          >
                            <option value="">Sélectionnez une ville</option>
                            <option v-for="villeItem in villesFiltered" :key="villeItem.id" :value="villeItem.name">
                              {{ villeItem.name }}
                            </option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Section: Documents -->
                  <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                      <i data-feather="file-text" class="feather-file-text me-2"></i>
                      <h5 class="mb-0">Documents</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <!-- CIN - Plus requis en modification -->
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="cin">
                            CIN <span class="text-muted">(Optionnel en modification)</span>
                          </label>
                          <div class="d-flex align-items-center mb-2">
                            <input
                              id="cin"
                              type="file"
                              class="form-control d-none"
                              @change="handleFileUpload($event, 'cin')"
                              accept="image/*,application/pdf"
                            />
                            <button
                              type="button"
                              class="btn"
                              :class="isEditMode ? 'btn-primary' : 'btn-secondary'"
                              :disabled="!isEditMode"
                              @click="triggerFileInput('cin')"
                            >
                              <i data-feather="upload" class="feather-upload me-1"></i>
                              {{ cinPreview ? 'Remplacer' : 'Télécharger' }}
                            </button>
                            <span class="ms-2 text-muted" v-if="form.cin && form.cin.name">{{ form.cin.name }}</span>
                            <span class="ms-2 text-success" v-else-if="existingCin">Fichier existant</span>
                            <span class="ms-2 text-muted" v-else>Aucun fichier</span>
                          </div>

                          <!-- CIN Preview -->
                          <div v-if="cinPreview" class="mt-2">
                            <div class="border rounded p-2" style="max-width: 300px">
                              <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Aperçu</span>
                                <button
                                  type="button"
                                  class="btn btn-sm"
                                  :class="isEditMode ? 'btn-danger' : 'btn-secondary'"
                                  :disabled="!isEditMode"
                                  @click="removeFile('cin')"
                                >
                                  <i data-feather="trash-2" class="feather-trash-2"></i>
                                </button>
                              </div>
                              <img
                                :src="cinPreview"
                                alt="CIN Preview"
                                style="max-height: 200px; max-width: 100%"
                              />
                            </div>
                          </div>
                        </div>

                        <!-- Certificat Négatif - Plus requis en modification -->
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="certificat_negative">
                            Certificat Négatif <span class="text-muted">(Optionnel en modification)</span>
                          </label>
                          <div class="d-flex align-items-center mb-2">
                            <input
                              id="certificat_negative"
                              type="file"
                              class="form-control d-none"
                              @change="handleFileUpload($event, 'certificat_negative')"
                              accept="image/*,application/pdf"
                            />
                            <button
                              type="button"
                              class="btn"
                              :class="isEditMode ? 'btn-primary' : 'btn-secondary'"
                              :disabled="!isEditMode"
                              @click="triggerFileInput('certificat_negative')"
                            >
                              <i data-feather="upload" class="feather-upload me-1"></i>
                              {{ certificatPreview ? 'Remplacer' : 'Télécharger' }}
                            </button>
                            <span class="ms-2 text-muted" v-if="form.certificat_negative && form.certificat_negative.name">{{ form.certificat_negative.name }}</span>
                            <span class="ms-2 text-success" v-else-if="existingCertificat">Fichier existant</span>
                            <span class="ms-2 text-muted" v-else>Aucun fichier</span>
                          </div>

                          <!-- Certificat Preview -->
                          <div v-if="certificatPreview" class="mt-2">
                            <div class="border rounded p-2" style="max-width: 300px">
                              <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Aperçu</span>
                                <button
                                  type="button"
                                  class="btn btn-sm"
                                  :class="isEditMode ? 'btn-danger' : 'btn-secondary'"
                                  :disabled="!isEditMode"
                                  @click="removeFile('certificat_negative')"
                                >
                                  <i data-feather="trash-2" class="feather-trash-2"></i>
                                </button>
                              </div>
                              <img
                                :src="certificatPreview"
                                alt="Certificat Preview"
                                style="max-height: 200px; max-width: 100%"
                              />
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
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="capital_social">
                            Capital Social (MAD) <span class="text-danger">*</span>
                          </label>
                          <div class="input-group">
                            <input
                              id="capital_social"
                              type="number"
                              class="form-control"
                              :class="{ 
                                'form-control-readonly': !isEditMode,
                                'is-invalid': errors.capital_social && isEditMode,
                                'is-valid': !errors.capital_social && form.capital_social && isEditMode
                              }"
                              v-model="form.capital_social"
                              :readonly="!isEditMode"
                              min="0"
                              step="0.01"
                              placeholder="0.00"
                              @input="handleCapitalSocialInput"
                              @keydown="preventMinus"
                              @focus="checkEditAccess"
                              autocomplete="off"
                            />
                            <span class="input-group-text">DH</span>
                          </div>
                          <div v-if="errors.capital_social && isEditMode" class="invalid-feedback">
                            {{ errors.capital_social }}
                          </div>
                        </div>
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="ice">
                            ICE <span class="text-danger">*</span>
                          </label>
                          <input
                            id="ice"
                            type="text"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.ice && isEditMode,
                              'is-valid': !errors.ice && form.ice && isEditMode
                            }"
                            v-model="form.ice"
                            :readonly="!isEditMode"
                            @input="handleICEInput"
                            @focus="checkEditAccess"
                            placeholder="000000000000000"
                            maxlength="15"
                          />
                          <div v-if="errors.ice && isEditMode" class="invalid-feedback">
                            {{ errors.ice }}
                          </div>
                        </div>
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="rc">
                            Registre de Commerce (RC) <span class="text-danger">*</span>
                          </label>
                          <input
                            id="rc"
                            type="text"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.rc && isEditMode,
                              'is-valid': !errors.rc && form.rc && isEditMode
                            }"
                            v-model="form.rc"
                            :readonly="!isEditMode"
                            @input="handleRCInput"
                            @focus="checkEditAccess"
                            placeholder="000000"
                            maxlength="8"
                          />
                          <div v-if="errors.rc && isEditMode" class="invalid-feedback">
                            {{ errors.rc }}
                          </div>
                        </div>
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="identifiant_fiscal">
                            Identifiant Fiscal <span class="text-danger">*</span>
                          </label>
                          <input
                            id="identifiant_fiscal"
                            type="text"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.identifiant_fiscal && isEditMode,
                              'is-valid': !errors.identifiant_fiscal && form.identifiant_fiscal && isEditMode
                            }"
                            v-model="form.identifiant_fiscal"
                            :readonly="!isEditMode"
                            @input="handleIdentifiantFiscalInput"
                            @focus="checkEditAccess"
                            placeholder="00000000"
                            maxlength="8"
                          />
                          <div v-if="errors.identifiant_fiscal && isEditMode" class="invalid-feedback">
                            {{ errors.identifiant_fiscal }}
                          </div>
                        </div>
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="tp">
                            Taxe Professionnelle (TP) <span class="text-danger">*</span>
                          </label>
                          <input
                            id="tp"
                            type="text"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.tp && isEditMode,
                              'is-valid': !errors.tp && form.tp && isEditMode
                            }"
                            v-model="form.tp"
                            :readonly="!isEditMode"
                            @input="handleTPInput"
                            @focus="checkEditAccess"
                            placeholder="00000000"
                            maxlength="8"
                          />
                          <div v-if="errors.tp && isEditMode" class="invalid-feedback">
                            {{ errors.tp }}
                          </div>
                        </div>
                        
                        <!-- Type Tribunal -->
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="type_tribunal">Type Tribunal</label>
                          <select 
                            id="type_tribunal" 
                            class="form-select" 
                            v-model="form.type_tribunal" 
                            @change="handleTypeTribunalChange"
                            :disabled="!isEditMode"
                            :class="{ 'form-control-readonly': !isEditMode }"
                          >
                            <option value="">----------</option>
                            <option v-for="type in typesTribunal" :key="type.id"
                              :value="type.id === '2' ? 'المحكمة الابتدائية' : 'المحكمة التجارية'">
                              {{ type.name }}
                            </option>
                          </select>
                        </div>

                        <!-- Tribunal -->
                        <div class="col-md-4 mb-3">
                          <label class="form-label" for="tribunal">Tribunal</label>
                          <select 
                            id="tribunal" 
                            class="form-select" 
                            v-model="form.tribunal"
                            :disabled="!form.type_tribunal || !isEditMode"
                            :class="{ 'form-control-readonly': !isEditMode }"
                          >
                            <option value="">----------</option>
                            <option v-for="tribunal in tribunauxFiltered" :key="tribunal.id" :value="tribunal.name">
                              {{ tribunal.name }}
                            </option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Section: Coordonnées -->
                  <div class="card mb-4">
                    <div class="card-header d-flex align-items-center">
                      <i data-feather="phone" class="feather-phone me-2"></i>
                      <h5 class="mb-0">Coordonnées</h5>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="telephone">
                            Téléphone <span class="text-danger">*</span>
                          </label>
                          <input
                            id="telephone"
                            type="text"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.telephone && isEditMode,
                              'is-valid': !errors.telephone && form.telephone && isEditMode
                            }"
                            v-model="form.telephone"
                            :readonly="!isEditMode"
                            @input="handleTelephoneInput"
                            @focus="checkEditAccess"
                            placeholder="+212 5 00 00 00 00"
                          />
                          <div v-if="errors.telephone && isEditMode" class="invalid-feedback">
                            {{ errors.telephone }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="email">
                            Email <span class="text-danger">*</span>
                          </label>
                          <input
                            id="email"
                            type="email"
                            class="form-control"
                            :class="{ 
                              'form-control-readonly': !isEditMode,
                              'is-invalid': errors.email && isEditMode,
                              'is-valid': !errors.email && form.email && isEditMode
                            }"
                            v-model="form.email"
                            :readonly="!isEditMode"
                            @input="handleEmailInput"
                            @focus="checkEditAccess"
                            placeholder="contact@example.com"
                          />
                          <div v-if="errors.email && isEditMode" class="invalid-feedback">
                            {{ errors.email }}
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label" for="website">Site web</label>
                          <input
                            id="website"
                            type="url"
                            class="form-control"
                            :class="{ 'form-control-readonly': !isEditMode }"
                            v-model="form.website"
                            :readonly="!isEditMode"
                            @focus="checkEditAccess"
                            placeholder="https://www.example.com"
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="text-end settings-bottom-btn mt-4">
                    <div class="btn-group">
                      <!-- Edit Button -->
                      <button 
                        v-if="!isEditMode"
                        type="button" 
                        class="btn btn-warning me-2"
                        @click="enableEditMode"
                      >
                        <i data-feather="edit-3" class="feather-edit-3 me-1"></i>
                        Éditer
                      </button>
                      
                      <!-- Cancel Button -->
                      <button 
                        v-if="isEditMode"
                        type="button" 
                        class="btn btn-secondary me-2"
                        @click="cancelEdit"
                      >
                        <i data-feather="x" class="feather-x me-1"></i>
                        Annuler
                      </button>
                      
                      <!-- Save Button -->
                      <button 
                        type="submit" 
                        class="btn"
                        :class="isEditMode ? (isFormValid ? 'btn-submit' : 'btn-danger') : 'btn-secondary'"
                        :disabled="!isEditMode || !isFormValid"
                      >
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
  </div>

  <!-- Modal pour ajouter un gérant -->
  <div v-if="showGerantModal" class="gerant-modal">
    <div class="gerant-modal-content">
      <div class="gerant-modal-header">
        <h5>{{ gerantModalMode === 'add' ? 'Ajouter un nouveau gérant' : 'Modifier le gérant' }}</h5>
        <button type="button" class="btn-close" @click="closeGerantModal">&times;</button>
      </div>
      <div class="gerant-modal-body">
        <form @submit.prevent="submitGerantForm">
          <div class="mb-3">
            <label class="form-label" for="gerant_nom">Nom *</label>
            <input
              id="gerant_nom"
              type="text"
              class="form-control"
              v-model="gerantForm.nom"
              placeholder="Nom complet"
              required
            />
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_cin">CIN *</label>
            <input
              id="gerant_cin"
              type="text"
              class="form-control"
              v-model="gerantForm.cin"
              placeholder="Numéro de CIN"
              required
            />
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_address">Adresse</label>
            <textarea
              id="gerant_address"
              class="form-control"
              v-model="gerantForm.address"
              placeholder="Adresse complète"
            ></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_date_naissance">Date de naissance</label>
            <input
              id="gerant_date_naissance"
              type="date"
              class="form-control"
              v-model="gerantForm.date_naissance"
            />
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_telephone">Téléphone</label>
            <input
              id="gerant_telephone"
              type="text"
              class="form-control"
              v-model="gerantForm.telephone"
              placeholder="+212 6 00 00 00 00"
            />
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_email">Email</label>
            <input
              id="gerant_email"
              type="email"
              class="form-control"
              v-model="gerantForm.email"
              placeholder="email@example.com"
            />
          </div>
          <div class="text-end mt-4">
            <button type="button" class="btn btn-secondary me-2" @click="closeGerantModal">
              Annuler
            </button>
            <button type="submit" class="btn btn-primary">
              {{ gerantModalMode === 'add' ? 'Ajouter' : 'Modifier' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style>
.keyboard-ar,
.keyboard-adresse {
  z-index: 2000;
  direction: rtl;
}

.card {
  border: 1px solid #e0e0e0;
  border-radius: 0.25rem;
}

.card-header {
  background-color: #f8f9fa;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.card-body {
  padding: 1rem;
}

.form-control-readonly {
  background-color: #f8f9fa;
  opacity: 1;
}

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

.text-danger {
  color: #dc3545 !important;
}

.text-success {
  color: #28a745 !important;
}

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

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  border-radius: 0.2rem;
}

.btn-submit {
  background-color: #28a745;
  color: white;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
}

.spinner-border {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  vertical-align: text-bottom;
  border: 0.25em solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spinner-border .75s linear infinite;
}

@keyframes spinner-border {
  to { transform: rotate(360deg); }
}

.visually-hidden {
  position: absolute !important;
  width: 1px !important;
  height: 1px !important;
  padding: 0 !important;
  margin: -1px !important;
  overflow: hidden !important;
  clip: rect(0, 0, 0, 0) !important;
  white-space: nowrap !important;
  border: 0 !important;
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

/* Styles pour le modal de gérant */
.gerant-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2100;
}

.gerant-modal-content {
  background-color: white;
  border-radius: 5px;
  width: 100%;
  max-width: 600px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  max-height: 90vh;
  overflow-y: auto;
}

.gerant-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.gerant-modal-body {
  padding: 1rem;
}

.modern-modal .btn-close {
  background: none; /* Supprimer l'arrière-plan gris */
  border: none;
  color: white; /* Couleur du X */
  font-size: 1.5rem; /* Taille du X */
  line-height: 1;
  opacity: 1;
  text-shadow: none;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modern-modal .btn-close::before {
  content: 'X'; /* Unicode for multiplication sign (X) */
  color: white;
  font-weight: bold;
}

.btn-close:hover {
  color: #000;
}

/* Bouton retour personnalisé sans animations */
.btn-back-custom {
  background-color: #ABC270 !important;
  border: 1px solid #ABC270 !important;
  color: white !important;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0;
}

.btn-back-custom:hover {
  background-color: #ABC270 !important;
  border-color: #ABC270 !important;
  color: white !important;
}

.btn-back-custom:active {
  background-color: #ABC270 !important;
  border-color: #ABC270 !important;
  color: white !important;
}

.btn-back-custom:focus {
  background-color: #ABC270 !important;
  border-color: #ABC270 !important;
  color: white !important;
  box-shadow: none;
}

.btn-back-custom i {
  width: 18px;
  height: 18px;
}

/* Style pour le titre "Modifier le client" */
.client-edit-title {
  color: #2c3e50;
  font-weight: 700;
  font-size: 1.75rem;
  margin-bottom: 0.5rem;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Responsive pour le bouton retour */
@media (max-width: 768px) {
  .btn-back-custom {
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
  }
  
  .client-edit-title {
    font-size: 1.5rem;
  }
}
</style>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from "vue"
import Keyboard from "simple-keyboard"
import "simple-keyboard/build/css/index.css"
import axios from "axios"
import Swal from "sweetalert2"
import tribunal from "@/assets/json/tribunal.json"
import type_tribunal from "@/assets/json/type_tribunal.json"
// Ajoutez ces imports pour les countries et cities
import countries from "@/assets/json/countries.json"
import cities from "@/assets/json/cities.json"

// Variables réactives existantes
const tribunauxPrimaires = ref([])
const tribunauxCommerciaux = ref([])
const typesTribunal = ref([])

// Nouvelles variables réactives pour countries et cities
const paysList = ref([])
const villesList = ref([])

// Computed property pour filtrer les cities selon le countries sélectionné
const villesFiltered = computed(() => {
  if (form.value.pays) {
    // Trouver le pays sélectionné pour obtenir son ID
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

// Fonction pour afficher les notifications d'erreur avec SweetAlert2
const showErrorToast = (message) => {
  Swal.fire({
    icon: "error",
    title: "Erreur!",
    text: message,
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 5000,
    timerProgressBar: true,
    background: '#f8d7da',
    color: '#721c24',
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  })
}

// Fonction pour mapper les noms de champs en français
const getFieldDisplayName = (fieldName) => {
  const fieldNames = {
    'ice': 'ICE',
    'rc': 'Registre de Commerce (RC)',
    'identifiant_fiscal': 'Identifiant Fiscal',
    'tp': 'Taxe Professionnelle (TP)',
    'email': 'Email',
    'telephone': 'Téléphone',
    'nom_francais': 'Nom Français',
    'nom_arabe': 'Nom Arabe'
  }
  return fieldNames[fieldName] || fieldName
}

// Fonction pour initialiser toutes les données
function initializeTribunalData() {
  // Charger les types de tribunal
  typesTribunal.value = type_tribunal

  // Séparer les tribunaux par type
  tribunauxPrimaires.value = tribunal.filter((t) => t.id_type === 2)
  tribunauxCommerciaux.value = tribunal.filter((t) => t.id_type === 1)

  // Charger les countries et cities
  paysList.value = countries
  villesList.value = cities

  console.log("Tribunaux primaires:", tribunauxPrimaires.value.length)
  console.log("Tribunaux commerciaux:", tribunauxCommerciaux.value.length)
  console.log("Pays chargés:", paysList.value.length)
  console.log("Villes chargées:", villesList.value.length)
}

const tribunauxFiltered = computed(() => {
  if (form.value.type_tribunal === "المحكمة الابتدائية") {
    return tribunauxPrimaires.value
  } else if (form.value.type_tribunal === "المحكمة التجارية") {
    return tribunauxCommerciaux.value
  } else {
    return []
  }
})

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
  gestionnaire_id: null,
})

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
  cin: "",
  certificat_negative: "",
})

// Pour le formulaire de gérant
const gerantForm = ref({
  nom: "",
  cin: "",
  address: "",
  date_naissance: "",
  telephone: "",
  email: "",
})

// Liste des gérants
const showGerantModal = ref(false)
const gerants = ref([])
const isLoadingGerants = ref(false)
const gerantModalMode = ref("add")

// For file previews
const cinPreview = ref(null)
const certificatPreview = ref(null)

const showArKeyboard = ref(false)
const showAdresseKeyboard = ref(false)
let keyboardAr = null
let keyboardAdresse = null

// Validation functions
const validateRequired = (value, fieldName) => {
  if (!value || value.toString().trim() === "") {
    return `${fieldName} est requis`
  }
  return ""
}

const validateFileRequired = (file, fieldName) => {
  if (!file) {
    return `${fieldName} est requis`
  }
  return ""
}

const validateArabicText = (text, fieldName) => {
  if (!text || text.trim() === "") {
    return `${fieldName} est requis`
  }

  const arabicRegex = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/
  if (!arabicRegex.test(text)) {
    return `${fieldName} doit contenir du texte en arabe`
  }

  if (text.trim().length < 2) {
    return `${fieldName} doit contenir au moins 2 caractères`
  }

  return ""
}

const validateEmail = (email) => {
  if (!email || email.trim() === "") {
    return "L'email est requis"
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email)) {
    return "Format d'email invalide"
  }

  return ""
}

const validatePhone = (phone) => {
  if (!phone || phone.trim() === "") {
    return "Le téléphone est requis"
  }

  const phoneRegex = /^(\+212|0)[5-7][0-9]{8}$|^[0-9]{9,10}$/
  if (!phoneRegex.test(phone.replace(/[\s-]/g, ""))) {
    return "Format de téléphone invalide (ex: +212 6 12 34 56 78)"
  }

  return ""
}

const validateCapitalSocial = (capital) => {
  if (!capital || capital.toString().trim() === "") {
    return "Le capital social est requis"
  }

  const numValue = Number.parseFloat(capital)
  if (isNaN(numValue) || numValue < 0) {
    return "Le capital social doit être un nombre positif"
  }

  return ""
}

const validateICE = (ice) => {
  if (!ice || ice.trim() === "") {
    return "L'ICE est requis"
  }
  // Nettoyer la valeur (supprimer espaces et caractères non numériques)
  const cleanIce = ice.replace(/\D/g, "")
  if (cleanIce.length !== 15) {
    return "L'ICE doit contenir exactement 15 chiffres"
  }
  return ""
}

const validateRC = (rc) => {
  if (!rc || rc.trim() === "") {
    return "Le RC est requis"
  }
  const cleanRC = rc.replace(/\D/g, "")
  if (cleanRC.length < 6 || cleanRC.length > 8) {
    return "Le RC doit contenir entre 6 et 8 chiffres"
  }
  return ""
}

const validateIdentifiantFiscal = (if_value) => {
  if (!if_value || if_value.trim() === "") {
    return "L'Identifiant Fiscal est requis"
  }
  const cleanIF = if_value.replace(/\D/g, "")
  if (cleanIF.length !== 8) {
    return "L'Identifiant Fiscal doit contenir exactement 8 chiffres"
  }
  return ""
}

const validateTP = (tp) => {
  if (!tp || tp.trim() === "") {
    return "La Taxe Professionnelle est requise"
  }
  const cleanTP = tp.replace(/\D/g, "")
  if (cleanTP.length !== 8) {
    return "La Taxe Professionnelle doit contenir exactement 8 chiffres"
  }
  return ""
}

// Validate all fields
const validateAllFields = () => {
  errors.value.nom_francais = validateRequired(form.value.nom_francais, "Le nom français")
  errors.value.nom_arabe = validateArabicText(form.value.nom_arabe, "Le nom arabe")
  errors.value.adresse_siege_social_francais = validateRequired(
    form.value.adresse_siege_social_francais,
    "L'adresse en français",
  )
  errors.value.adresse_siege_social_arabe = validateArabicText(
    form.value.adresse_siege_social_arabe,
    "L'adresse en arabe",
  )
  errors.value.capital_social = validateCapitalSocial(form.value.capital_social)
  errors.value.ice = validateICE(form.value.ice)
  errors.value.rc = validateRC(form.value.rc)
  errors.value.identifiant_fiscal = validateIdentifiantFiscal(form.value.identifiant_fiscal)
  errors.value.tp = validateTP(form.value.tp)
  errors.value.telephone = validatePhone(form.value.telephone)
  errors.value.email = validateEmail(form.value.email)
  errors.value.cin = validateFileRequired(form.value.cin, "Le CIN")
  errors.value.certificat_negative = validateFileRequired(form.value.certificat_negative, "Le certificat négatif")
}

// Computed property to check if form is valid
const isFormValid = computed(() => {
  return Object.values(errors.value).every((error) => error === "")
})

onMounted(() => {
  document.addEventListener("mousedown", handleClickOutside)
  fetchGerants()
  initializeTribunalData()
})

onBeforeUnmount(() => {
  document.removeEventListener("mousedown", handleClickOutside)
})

// Fonction pour gérer le changement de countries
function handlePaysChange() {
  console.log("Pays sélectionné:", form.value.pays)
  form.value.ville = ""
}

// Récupérer la liste des gérants
async function fetchGerants() {
  try {
    isLoadingGerants.value = true
    const response = await axios.get("/gerants")
    gerants.value = response.data
    console.log("Gérants récupérés:", gerants.value)
  } catch (error) {
    console.error("Erreur lors de la récupération des gérants:", error)
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: "Impossible de charger la liste des gérants",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
      background: '#f8d7da',
      color: '#721c24'
    })
  } finally {
    isLoadingGerants.value = false
  }
}

// Ouvrir le modal pour ajouter un gérant
function openAddGerantModal() {
  gerantForm.value = {
    nom: "",
    cin: "",
    address: "",
    date_naissance: "",
    telephone: "",
    email: "",
  }
  gerantModalMode.value = "add"
  showGerantModal.value = true
  form.value.gerant_id = null
}

// Fermer le modal de gérant
function closeGerantModal() {
  showGerantModal.value = false
}

// Soumettre le formulaire de gérant
async function submitGerantForm() {
  try {
    console.log("Données du gérant à envoyer:", gerantForm.value)
    const response = await axios.post("/gerants", gerantForm.value)
    console.log("Réponse du serveur:", response.data)

    const newGerant = response.data.data
    gerants.value.push(newGerant)
    await nextTick()
    form.value.gerant_id = newGerant.id

    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Gérant ajouté avec succès",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      background: '#d4edda',
      color: '#155724'
    })

    closeGerantModal()
  } catch (error) {
    console.error("Erreur lors de l'ajout du gérant:", error)
    let errorMessage = "Impossible d'ajouter le gérant"
    if (error.response && error.response.data && error.response.data.message) {
      errorMessage = error.response.data.message
    } else if (error.response && error.response.data && error.response.data.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage = errors.join(", ")
    }

    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: errorMessage,
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
      background: '#f8d7da',
      color: '#721c24'
    })
  }
}

function handleGerantChange(event) {
  const value = event.target.value
  console.log("Valeur sélectionnée:", value)

  if (value === "add_new") {
    event.target.value = ""
    form.value.gerant_id = null
    openAddGerantModal()
  } else {
    form.value.gerant_id = value
  }
}

// Input handlers with validation
const handleNomFrancaisInput = (event) => {
  form.value.nom_francais = event.target.value
  errors.value.nom_francais = validateRequired(form.value.nom_francais, "Le nom français")
}

const handleNomArabeInput = (value) => {
  form.value.nom_arabe = value
  errors.value.nom_arabe = validateArabicText(form.value.nom_arabe, "Le nom arabe")
}

const handleCapitalSocialInput = (event) => {
  let value = event.target.value.replace(/^0+(?=\d)/, "")
  value = value.replace(/[^0-9.]/g, "")
  value = value.replace(/(\..*)\./g, "$1")

  form.value.capital_social = value
  errors.value.capital_social = validateCapitalSocial(form.value.capital_social)
}

// Nouvelles fonctions pour gérer les champs problématiques
const handleICEInput = (event) => {
  // Garder seulement les chiffres
  let value = event.target.value.replace(/\D/g, "")

  // Limiter à 15 caractères
  if (value.length > 15) {
    value = value.substring(0, 15)
  }

  form.value.ice = value
  errors.value.ice = validateICE(form.value.ice)
}

const handleIdentifiantFiscalInput = (event) => {
  // Garder seulement les chiffres
  let value = event.target.value.replace(/\D/g, "")

  // Limiter à 8 caractères
  if (value.length > 8) {
    value = value.substring(0, 8)
  }

  form.value.identifiant_fiscal = value
  errors.value.identifiant_fiscal = validateIdentifiantFiscal(form.value.identifiant_fiscal)
}

const handleTPInput = (event) => {
  // Garder seulement les chiffres
  let value = event.target.value.replace(/\D/g, "")

  // Limiter à 8 caractères
  if (value.length > 8) {
    value = value.substring(0, 8)
  }

  form.value.tp = value
  errors.value.tp = validateTP(form.value.tp)
}

const handleRCInput = (event) => {
  // Garder seulement les chiffres
  let value = event.target.value.replace(/\D/g, "")

  // Limiter à 8 caractères
  if (value.length > 8) {
    value = value.substring(0, 8)
  }

  form.value.rc = value
  errors.value.rc = validateRC(form.value.rc)
}

const handleTelephoneInput = (event) => {
  form.value.telephone = event.target.value
  errors.value.telephone = validatePhone(form.value.telephone)
}

const handleEmailInput = (event) => {
  form.value.email = event.target.value
  errors.value.email = validateEmail(form.value.email)
}

const handleAdresseFrancaisInput = (event) => {
  form.value.adresse_siege_social_francais = event.target.value
  errors.value.adresse_siege_social_francais = validateRequired(
    form.value.adresse_siege_social_francais,
    "L'adresse en français",
  )
}

function onInput(field, event) {
  const value = event.target.value

  if (field === "nom_arabe") {
    handleNomArabeInput(value)
    if (keyboardAr) keyboardAr.setInput(form.value.nom_arabe)
  } else if (field === "adresse_siege_social_arabe") {
    form.value.adresse_siege_social_arabe = value
    errors.value.adresse_siege_social_arabe = validateArabicText(value, "L'adresse en arabe")
    if (keyboardAdresse) keyboardAdresse.setInput(form.value.adresse_siege_social_arabe)
  }
}

function onFocusKeyboard(type) {
  if (type === "ar") {
    showArKeyboard.value = true
    if (!keyboardAr) {
      setTimeout(() => {
        keyboardAr = new Keyboard(".keyboard-ar", {
          layout: {
            default: ["ض ص ث ق ف غ ع ه خ ح ج د", "ش س ي ب ل ا ت ن م ك ط", "ئ ء ؤ ر لا ى ة و ز ظ", "{space}"],
          },
          display: {
            "{space}": "مسافة",
          },
          rtl: true,
          onChange: (input) => {
            handleNomArabeInput(input)
          },
          inputName: "nom_arabe",
        })
        keyboardAr.setInput(form.value.nom_arabe)
      }, 0)
    }
  }
  if (type === "adresse") {
    showAdresseKeyboard.value = true
    if (!keyboardAdresse) {
      setTimeout(() => {
        keyboardAdresse = new Keyboard(".keyboard-adresse", {
          layout: {
            default: ["ض ص ث ق ف غ ع ه خ ح ج د", "ش س ي ب ل ا ت ن م ك ط", "ئ ء ؤ ر لا ى ة و ز ظ", "{space}"],
          },
          display: {
            "{space}": "مسافة",
          },
          rtl: true,
          onChange: (input) => {
            form.value.adresse_siege_social_arabe = input
            errors.value.adresse_siege_social_arabe = validateArabicText(input, "L'adresse en arabe")
          },
          inputName: "adresse_siege_social_arabe",
        })
        keyboardAdresse.setInput(form.value.adresse_siege_social_arabe)
      }, 0)
    }
  }
}

function handleClickOutside(e) {
  const arInput = document.getElementById("nom_arabe")
  const arKeyboard = document.querySelector(".keyboard-ar")
  if (showArKeyboard.value && arInput && arKeyboard && !arInput.contains(e.target) && !arKeyboard.contains(e.target)) {
    showArKeyboard.value = false
  }
  const adresseInput = document.getElementById("adresse_siege_social_arabe")
  const adresseKeyboard = document.querySelector(".keyboard-adresse")
  if (
    showAdresseKeyboard.value &&
    adresseInput &&
    adresseKeyboard &&
    !adresseInput.contains(e.target) &&
    !adresseKeyboard.contains(e.target)
  ) {
    showAdresseKeyboard.value = false
  }

  const gerantModal = document.querySelector(".gerant-modal")
  const gerantModalContent = document.querySelector(".gerant-modal-content")
  if (
    showGerantModal.value &&
    gerantModal &&
    gerantModalContent &&
    !gerantModalContent.contains(e.target) &&
    e.target.className !== "add-gerant-btn" &&
    !e.target.closest(".add-gerant-btn")
  ) {
    showGerantModal.value = false
  }
}

function triggerFileInput(inputId) {
  document.getElementById(inputId).click()
}

function handleFileUpload(event, field) {
  const file = event.target.files[0]
  if (file && file.size <= 5 * 1024 * 1024) {
    form.value[field] = file
    errors.value[field] = "" // Réinitialiser l'erreur

    const reader = new FileReader()
    reader.onload = (e) => {
      if (field === "cin") {
        cinPreview.value = e.target.result
      } else if (field === "certificat_negative") {
        certificatPreview.value = e.target.result
      }
    }
    reader.readAsDataURL(file)
  } else {
    Swal.fire({
      icon: "warning",
      title: "Attention",
      text: "Fichier trop volumineux (max 5MB)",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 4000,
      timerProgressBar: true,
      background: '#fff3cd',
      color: '#856404'
    })
    event.target.value = null
  }
}

function removeFile(field) {
  form.value[field] = null
  if (field === "cin") {
    cinPreview.value = null
  } else if (field === "certificat_negative") {
    certificatPreview.value = null
  }
  const fileInput = document.getElementById(field)
  if (fileInput) fileInput.value = ""
}

async function submitForm() {
  // Valider tous les champs avant soumission
  validateAllFields()

  if (!isFormValid.value) {
    const errorFields = Object.keys(errors.value).filter((key) => errors.value[key] !== "")
    const errorMessages = errorFields.map((field) => errors.value[field]).join(', ')
    
    Swal.fire({
      icon: "warning",
      title: "Erreurs de validation",
      text: `Veuillez corriger les erreurs suivantes: ${errorMessages}`,
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 6000,
      timerProgressBar: true,
      background: '#fff3cd',
      color: '#856404'
    })
    return
  }

  try {
    console.log("Préparation des données du formulaire...")
    const formData = new FormData()

    if (!form.value.type_tribunal) {
      Swal.fire({
        icon: "warning",
        title: "Attention",
        text: "Veuillez sélectionner un type de tribunal",
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        background: '#fff3cd',
        color: '#856404'
      })
      return
    }

    if (!form.value.tribunal) {
      Swal.fire({
        icon: "warning",
        title: "Attention",
        text: "Veuillez sélectionner un tribunal",
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        background: '#fff3cd',
        color: '#856404'
      })
      return
    }

    Object.keys(form.value).forEach((key) => {
      if (form.value[key] !== null && form.value[key] !== undefined && form.value[key] !== "") {
        formData.append(key, form.value[key])
        console.log(`Ajout au FormData: ${key}`, form.value[key])
      }
    })

    const response = await axios.post("/clients", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
        Accept: "application/json",
      },
    })

    console.log("Réponse du serveur:", response.data)

    Swal.fire({
      icon: "success",
      title: "Succès!",
      text: "Client ajouté avec succès!",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      background: '#d4edda',
      color: '#155724'
    })

    resetForm()
  } catch (e) {
    console.error("Erreur détaillée:", e)

    // Fonction pour détecter les erreurs de contrainte d'intégrité
    const detectDuplicateError = (errorMessage) => {
      if (!errorMessage) return null

      // Détecter les erreurs de duplicate entry
      if (errorMessage.includes('Duplicate entry') && errorMessage.includes('for key')) {
        // Extraire le nom de la clé unique - chercher le pattern plus flexible
        let keyMatch = errorMessage.match(/for key '.*?_(\w+)_unique'/i)
        if (keyMatch) {
          let field = keyMatch[1]
          // Supprimer le préfixe de table s'il existe
          if (field.startsWith('clients_')) {
            field = field.replace('clients_', '')
          }
          const fieldDisplayName = getFieldDisplayName(field)
          return `${fieldDisplayName}: Cette valeur existe déjà dans la base de données. Veuillez utiliser une valeur différente.`
        }

        // Si le pattern précédent ne fonctionne pas, essayer un autre pattern
        keyMatch = errorMessage.match(/for key '(\w+)_unique'/i)
        if (keyMatch) {
          let fullKey = keyMatch[1]
          // Supprimer le préfixe "clients_" s'il existe
          if (fullKey.startsWith('clients_')) {
            fullKey = fullKey.replace('clients_', '')
          }
          // Enlever le préfixe de table (tout ce qui est avant le dernier underscore)
          const field = fullKey.includes('_') ? fullKey.split('_').pop() : fullKey
          const fieldDisplayName = getFieldDisplayName(field)
          return `${fieldDisplayName}: Cette valeur existe déjà dans la base de données. Veuillez utiliser une valeur différente.`
        }

        // Cas spécifiques pour les champs principaux (sans préfixe)
        if (errorMessage.includes('ice_unique') || errorMessage.includes('clients_ice_unique')) {
          return "ICE: Cette valeur existe déjà dans la base de données. Veuillez utiliser un ICE différent."
        }
        if (errorMessage.includes('rc_unique') || errorMessage.includes('clients_rc_unique')) {
          return "Registre de Commerce (RC): Cette valeur existe déjà dans la base de données. Veuillez utiliser un RC différent."
        }
        if (errorMessage.includes('identifiant_fiscal_unique') || errorMessage.includes('clients_identifiant_fiscal_unique')) {
          return "Identifiant Fiscal: Cette valeur existe déjà dans la base de données. Veuillez utiliser un identifiant différent."
        }
        if (errorMessage.includes('tp_unique') || errorMessage.includes('clients_tp_unique')) {
          return "Taxe Professionnelle (TP): Cette valeur existe déjà dans la base de données. Veuillez utiliser une TP différente."
        }
        if (errorMessage.includes('email_unique') || errorMessage.includes('clients_email_unique')) {
          return "Email: Cette adresse email existe déjà dans la base de données. Veuillez utiliser une adresse différente."
        }

        return "Une valeur que vous avez saisie existe déjà dans la base de données. Veuillez vérifier vos informations."
      }

      return null
    }

    // Gestion spécifique des erreurs de validation (422)
    if (e.response && e.response.status === 422) {
      const serverErrors = e.response.data.errors || {}
      
      // Vérifier s'il y a des erreurs d'unicité pour les champs spécifiques
      const uniqueFields = ["ice", "rc", "identifiant_fiscal", "tp", "email"]
      const uniqueErrors = []
      
      uniqueFields.forEach(field => {
        if (serverErrors[field]) {
          const fieldDisplayName = getFieldDisplayName(field)
          uniqueErrors.push(`${fieldDisplayName}: Cette valeur existe déjà dans la base de données`)
          
          // Mettre à jour les erreurs du formulaire
          if (errors.value.hasOwnProperty(field)) {
            errors.value[field] = "Cette valeur existe déjà"
          }
        }
      })
      
      // S'il y a des erreurs d'unicité, les afficher avec SweetAlert2 toast
      if (uniqueErrors.length > 0) {
        Swal.fire({
          icon: "error",
          title: "Données déjà existantes",
          html: uniqueErrors.join('<br>'),
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 6000,
          timerProgressBar: true,
          background: '#f8d7da',
          color: '#721c24',
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })
      } 
      // Pour les autres erreurs de validation
      else {
        const otherErrors = []
        Object.keys(serverErrors).forEach(field => {
          const fieldDisplayName = getFieldDisplayName(field)
          otherErrors.push(`${fieldDisplayName}: ${serverErrors[field][0]}`)
          
          // Mettre à jour les erreurs du formulaire
          if (errors.value.hasOwnProperty(field)) {
            errors.value[field] = serverErrors[field][0]
          }
        })
        
        if (otherErrors.length > 0) {
          Swal.fire({
            icon: "warning",
            title: "Erreur de validation",
            html: otherErrors.join('<br>'),
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            background: '#fff3cd',
            color: '#856404'
          })
        }
      }
    } 
    // Gestion des erreurs de contrainte d'intégrité (erreurs de base de données)
    else if (e.response && (e.response.status === 500 || e.response.status === 400)) {
      let errorMessage = ""
      
      // Essayer d'extraire le message d'erreur
      if (e.response.data && e.response.data.message) {
        errorMessage = e.response.data.message
      } else if (e.response.data && typeof e.response.data === 'string') {
        errorMessage = e.response.data
      } else if (e.message) {
        errorMessage = e.message
      }
      
      // Détecter les erreurs de duplicate
      const duplicateError = detectDuplicateError(errorMessage)
      
      if (duplicateError) {
        Swal.fire({
          icon: "error",
          title: "Données déjà existantes",
          text: duplicateError,
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 6000,
          timerProgressBar: true,
          background: '#f8d7da',
          color: '#721c24',
          didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
          }
        })
      } else {
        // Autres erreurs serveur
        Swal.fire({
          icon: "error",
          title: "Erreur serveur",
          text: "Une erreur est survenue lors de l'enregistrement. Veuillez réessayer ou contacter l'administrateur.",
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 5000,
          timerProgressBar: true,
          background: '#f8d7da',
          color: '#721c24'
        })
      }
    }
    // Gestion des autres types d'erreurs
    else {
      let errorMessage = "Erreur lors de l'ajout du client."
      
      if (e.response) {
        if (e.response.data && e.response.data.message) {
          errorMessage = e.response.data.message
        } else if (e.response.data && e.response.data.error) {
          errorMessage = e.response.data.error
        }
        
        // Vérifier si c'est une erreur de duplicate même dans d'autres cas
        const duplicateError = detectDuplicateError(errorMessage)
        if (duplicateError) {
          Swal.fire({
            icon: "error",
            title: "Données déjà existantes",
            text: duplicateError,
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 6000,
            timerProgressBar: true,
            background: '#f8d7da',
            color: '#721c24'
          })
          return
        }
      } else if (e.request) {
        errorMessage = "Aucune réponse du serveur. Veuillez vérifier votre connexion."
      } else {
        errorMessage = "Erreur lors de la préparation de la requête."
      }

      Swal.fire({
        icon: "error",
        title: "Erreur!",
        text: errorMessage,
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        background: '#f8d7da',
        color: '#721c24'
      })
    }
  }
}

function resetForm() {
  form.value = {
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
    gestionnaire_id: null,
  }
  cinPreview.value = null
  certificatPreview.value = null

  // Réinitialiser les erreurs
  errors.value = {
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
    cin: "",
    certificat_negative: "",
  }
}

function preventMinus(e) {
  if (e.key === "-" || e.key === "e") e.preventDefault()
}

function handleTypeTribunalChange() {
  form.value.tribunal = ""
}

// Réinitialiser le type de client quand on change le type d'entreprise
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
            <h4>Gestion des Clients</h4>
            <h6>Ajouter un nouveau client</h6>
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
                  <div class="badge bg-primary">
                    <i data-feather="plus" class="feather-plus me-1"></i>
                    Nouveau client
                  </div>
                </div>

                <!-- Section: Informations générales -->
                <div class="card mb-4">
                  <div class="card-header d-flex align-items-center">
                    <i data-feather="info\" class="feather-info me-2"></i>
                    <h5 class="mb-0">Informations générales</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Type d'entreprise</label>
                        <select class="form-select" v-model="form.type_entreprise">
                          <option value="MORALE">Morale</option>
                          <option value="PHYSIQUE">Physique</option>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Type de client</label>
                        <select class="form-select" v-model="form.type_client">
                          <option v-for="type in filteredClientTypes" :key="type" :value="type">
                            {{ type }}
                          </option>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Statut</label>
                        <select class="form-select" v-model="form.status">
                          <option value="PROSPECT">Prospect</option>
                          <option value="ACTIVE">Actif</option>
                          <option value="INACTIVE">Inactif</option>
                        </select>
                      </div>
                      
                      <!-- Nom Français avec validation -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="nom_francais">
                          Nom Français <span class="text-danger">*</span>
                        </label>
                        <input
                          id="nom_francais"
                          type="text"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.nom_francais,
                            'is-valid': !errors.nom_francais && form.nom_francais
                          }"
                          v-model="form.nom_francais"
                          @input="handleNomFrancaisInput"
                          placeholder="Entrez le nom en français"
                        />
                        <div v-if="errors.nom_francais" class="invalid-feedback">
                          {{ errors.nom_francais }}
                        </div>
                      </div>
                      
                      <!-- Nom Arabe avec validation -->
                      <div class="col-md-6 mb-3\">
                        <label class="form-label" for="nom_arabe">
                          Nom Arabe <span class="text-danger">*</span>
                        </label>
                        <input
                          id="nom_arabe"
                          type="text"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.nom_arabe,
                            'is-valid': !errors.nom_arabe && form.nom_arabe
                          }"
                          :value="form.nom_arabe"
                          @input="onInput('nom_arabe', $event)"
                          @focus="onFocusKeyboard('ar')"
                          style="direction: rtl"
                          placeholder="أدخل الاسم بالعربية"
                        />
                        <div v-if="errors.nom_arabe" class="invalid-feedback">
                          {{ errors.nom_arabe }}
                        </div>
                        <div v-show="showArKeyboard" class="keyboard-ar mt-2"></div>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="gerant_id">Gérant</label>
                        <div class="input-group">
                          <select id="gerant_id" class="form-select" v-model="form.gerant_id"
                            @change="handleGerantChange">
                            <option value="" disabled>Sélectionnez un gérant</option>
                            <option v-for="gerant in gerants" :key="gerant.id" :value="gerant.id">
                              {{ gerant.nom }}
                            </option>
                            <option value="add_new" class="text-primary fw-bold\">+ Ajouter un nouveau gérant</option>
                          </select>
                          <button type="button" class="btn btn-outline-primary add-gerant-btn"
                            @click="openAddGerantModal">
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
                      <!-- Adresse Français avec validation -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="adresse_siege_social_francais">
                          Adresse Siège Social (FR) <span class="text-danger">*</span>
                        </label>
                        <textarea
                          id="adresse_siege_social_francais"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.adresse_siege_social_francais,
                            'is-valid': !errors.adresse_siege_social_francais && form.adresse_siege_social_francais
                          }"
                          v-model="form.adresse_siege_social_francais"
                          @input="handleAdresseFrancaisInput"
                          placeholder="Entrez l'adresse en français"
                        ></textarea>
                        <div v-if="errors.adresse_siege_social_francais" class="invalid-feedback">
                          {{ errors.adresse_siege_social_francais }}
                        </div>
                      </div>
                      
                      <!-- Adresse Arabe avec validation -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="adresse_siege_social_arabe">
                          Adresse Siège Social (AR) <span class="text-danger">*</span>
                        </label>
                        <textarea
                          id="adresse_siege_social_arabe"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.adresse_siege_social_arabe,
                            'is-valid': !errors.adresse_siege_social_arabe && form.adresse_siege_social_arabe
                          }"
                          :value="form.adresse_siege_social_arabe"
                          @input="onInput('adresse_siege_social_arabe', $event)"
                          @focus="onFocusKeyboard('adresse')"
                          style="direction: rtl"
                          placeholder="أدخل العنوان بالعربية"
                        ></textarea>
                        <div v-if="errors.adresse_siege_social_arabe" class="invalid-feedback">
                          {{ errors.adresse_siege_social_arabe }}
                        </div>
                        <div v-show="showAdresseKeyboard" class="keyboard-adresse mt-2"></div>
                      </div>
                      
                      <!-- Champs countries et ville modifiés -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="pays">Pays</label>
                        <select id="pays" class="form-control" v-model="form.pays" @change="handlePaysChange">
                          <option value="">Sélectionnez un pays</option>
                          <option v-for="paysItem in paysList" :key="paysItem.id" :value="paysItem.name">
                            {{ paysItem.name }}
                          </option>
                        </select>
                        <small class="text-muted">Pays sélectionné: {{ form.pays }}</small>
                      </div>
                      
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="ville">Ville</label>
                        <select id="ville" class="form-control" v-model="form.ville" :disabled="!form.pays">
                          <option value="">Sélectionnez une ville</option>
                          <option v-for="villeItem in villesFiltered" :key="villeItem.id" :value="villeItem.name">
                            {{ villeItem.name }}
                          </option>
                        </select>
                        <small class="text-muted">
                          Villes disponibles: {{ villesFiltered.length }} | 
                          Ville sélectionnée: {{ form.ville }}
                        </small>
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
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="cin">
                          CIN <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex align-items-center mb-2">
                          <input id="cin" type="file" class="form-control d-none"
                            @change="handleFileUpload($event, 'cin')" accept="image/*,application/pdf" />
                          <button type="button" class="btn btn-primary" @click="triggerFileInput('cin')">
                            <i data-feather="upload" class="feather-upload me-1"></i>
                            Télécharger
                          </button>
                          <span class="ms-2 text-muted" v-if="form.cin">{{ form.cin.name }}</span>
                          <span class="ms-2 text-muted" v-else>Aucun fichier sélectionné</span>
                        </div>
                        <small class="text-muted d-block mb-2">Format: PDF, JPG, PNG. Max 5MB</small>
                        <div v-if="errors.cin" class="text-danger">{{ errors.cin }}</div>

                        <div v-if="cinPreview" class="mt-2">
                          <div class="border rounded p-2" style="max-width: 300px">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="fw-bold">Aperçu</span>
                              <button type="button" class="btn btn-sm btn-danger" @click="removeFile('cin')">
                                <i data-feather="trash-2" class="feather-trash-2"></i>
                              </button>
                            </div>
                            <embed v-if="cinPreview.includes('application/pdf')" :src="cinPreview"
                              type="application/pdf" width="100%" height="200px" />
                            <img v-else :src="cinPreview" alt="CIN Preview"
                              style="max-height: 200px; max-width: 100%" />
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="certificat_negative">
                          Certificat Négatif <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex align-items-center mb-2">
                          <input id="certificat_negative" type="file" class="form-control d-none"
                            @change="handleFileUpload($event, 'certificat_negative')"
                            accept="image/*,application/pdf" />
                          <button type="button" class="btn btn-primary"
                            @click="triggerFileInput('certificat_negative')">
                            <i data-feather="upload" class="feather-upload me-1"></i>
                            Télécharger
                          </button>
                          <span class="ms-2 text-muted" v-if="form.certificat_negative">{{ form.certificat_negative.name }}</span>
                          <span class="ms-2 text-muted" v-else>Aucun fichier sélectionné</span>
                        </div>
                        <small class="text-muted d-block mb-2">Format: PDF, JPG, PNG. Max 5MB</small>
                        <div v-if="errors.certificat_negative" class="text-danger">{{ errors.certificat_negative }}</div>

                        <div v-if="certificatPreview" class="mt-2">
                          <div class="border rounded p-2" style="max-width: 300px">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="fw-bold">Aperçu</span>
                              <button type="button" class="btn btn-sm btn-danger"
                                @click="removeFile('certificat_negative')">
                                <i data-feather="trash-2" class="feather-trash-2"></i>
                              </button>
                            </div>
                            <embed v-if="certificatPreview.includes('application/pdf')" :src="certificatPreview"
                              type="application/pdf" width="100%" height="200px" />
                            <img v-else :src="certificatPreview" alt="Certificat Preview"
                              style="max-height: 200px; max-width: 100%" />
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
                      <!-- Capital Social avec validation -->
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
                              'is-invalid': errors.capital_social,
                              'is-valid': !errors.capital_social && form.capital_social
                            }"
                            v-model="form.capital_social"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            @input="handleCapitalSocialInput"
                            @keydown="preventMinus"
                            autocomplete="off"
                          />
                          <span class="input-group-text">DH</span>
                        </div>
                        <div v-if="errors.capital_social" class="invalid-feedback">
                          {{ errors.capital_social }}
                        </div>
                      </div>
                      
                      <!-- ICE avec validation -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="ice">
                          ICE <span class="text-danger">*</span>
                        </label>
                        <input
                          id="ice"
                          type="text"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.ice,
                            'is-valid': !errors.ice && form.ice
                          }"
                          v-model="form.ice"
                          @input="handleICEInput"
                          placeholder="000000000000000"
                          maxlength="15"
                        />
                        <div v-if="errors.ice" class="invalid-feedback">
                          {{ errors.ice }}
                        </div>
                      </div>
                      
                      <!-- RC avec validation -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="rc">
                          Registre de Commerce (RC) <span class="text-danger">*</span>
                        </label>
                        <input
                          id="rc"
                          type="text"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.rc,
                            'is-valid': !errors.rc && form.rc
                          }"
                          v-model="form.rc"
                          @input="handleRCInput"
                          placeholder="000000"
                          maxlength="8"
                        />
                        <div v-if="errors.rc" class="invalid-feedback">
                          {{ errors.rc }}
                        </div>
                      </div>
                      
                      <!-- Identifiant Fiscal avec validation -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="identifiant_fiscal">
                          Identifiant Fiscal <span class="text-danger">*</span>
                        </label>
                        <input
                          id="identifiant_fiscal"
                          type="text"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.identifiant_fiscal,
                            'is-valid': !errors.identifiant_fiscal && form.identifiant_fiscal
                          }"
                          v-model="form.identifiant_fiscal"
                          @input="handleIdentifiantFiscalInput"
                          placeholder="00000000"
                          maxlength="8"
                        />
                        <div v-if="errors.identifiant_fiscal" class="invalid-feedback">
                          {{ errors.identifiant_fiscal }}
                        </div>
                      </div>
                      
                      <!-- TP avec validation -->
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="tp">
                          Taxe Professionnelle (TP) <span class="text-danger">*</span>
                        </label>
                        <input
                          id="tp"
                          type="text"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.tp,
                            'is-valid': !errors.tp && form.tp
                          }"
                          v-model="form.tp"
                          @input="handleTPInput"
                          placeholder="00000000"
                          maxlength="8"
                        />
                        <div v-if="errors.tp" class="invalid-feedback">
                          {{ errors.tp }}
                        </div>
                      </div>
                      
                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="type_tribunal">Type Tribunal *</label>
                        <select id="type_tribunal" class="form-control" v-model="form.type_tribunal" required
                          @change="handleTypeTribunalChange">
                          <option value="">----------</option>
                          <option v-for="type in typesTribunal" :key="type.id"
                            :value="type.id === '2' ? 'المحكمة الابتدائية' : 'المحكمة التجارية'">
                            {{ type.name }}
                          </option>
                        </select>
                      </div>

                      <div class="col-md-4 mb-3">
                        <label class="form-label" for="tribunal">Tribunal *</label>
                        <select id="tribunal" class="form-control" v-model="form.tribunal" required
                          :disabled="!form.type_tribunal">
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
                      <!-- Téléphone avec validation -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="telephone">
                          Téléphone <span class="text-danger">*</span>
                        </label>
                        <input
                          id="telephone"
                          type="text"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.telephone,
                            'is-valid': !errors.telephone && form.telephone
                          }"
                          v-model="form.telephone"
                          @input="handleTelephoneInput"
                          placeholder="+212 5 00 00 00 00"
                        />
                        <div v-if="errors.telephone" class="invalid-feedback">
                          {{ errors.telephone }}
                        </div>
                      </div>
                      
                      <!-- Email avec validation -->
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">
                          Email <span class="text-danger">*</span>
                        </label>
                        <input
                          id="email"
                          type="email"
                          class="form-control"
                          :class="{ 
                            'is-invalid': errors.email,
                            'is-valid': !errors.email && form.email
                          }"
                          v-model="form.email"
                          @input="handleEmailInput"
                          placeholder="contact@example.com"
                        />
                        <div v-if="errors.email" class="invalid-feedback">
                          {{ errors.email }}
                        </div>
                      </div>
                      
                      <div class="col-md-6 mb-3">
                        <label class="form-label" for="website">Site web</label>
                        <input id="website" type="url" class="form-control" v-model="form.website"
                          placeholder="https://www.example.com" />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-end settings-bottom-btn mt-4">
                  <div class="btn-group">
                    <button type="button" class="btn btn-secondary me-2" @click="resetForm">
                      <i data-feather="refresh-cw" class="feather-refresh-cw me-1"></i>
                      Réinitialiser
                    </button>
                    <button 
                      type="submit" 
                      class="btn"
                      :class="isFormValid ? 'btn-submit' : 'btn-danger'"
                      :disabled="!isFormValid"
                    >
                      <i data-feather="save" class="feather-save me-1"></i>
                      Enregistrer
                    </button>
                  </div>
                  
                  <!-- Validation Summary -->
                  <div v-if="!isFormValid" class="mt-3">
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
            <input id="gerant_nom" type="text" class="form-control" v-model="gerantForm.nom" placeholder="Nom complet"
              required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_cin">CIN *</label>
            <input id="gerant_cin" type="text" class="form-control" v-model="gerantForm.cin" placeholder="Numéro de CIN"
              required />
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_address">Adresse</label>
            <textarea id="gerant_address" class="form-control" v-model="gerantForm.address"
              placeholder="Adresse complète"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_date_naissance">Date de naissance</label>
            <input id="gerant_date_naissance" type="date" class="form-control" v-model="gerantForm.date_naissance" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_telephone">Téléphone</label>
            <input id="gerant_telephone" type="text" class="form-control" v-model="gerantForm.telephone"
              placeholder="+212 6 00 00 00 00" />
          </div>
          <div class="mb-3">
            <label class="form-label" for="gerant_email">Email</label>
            <input id="gerant_email" type="email" class="form-control" v-model="gerantForm.email"
              placeholder="email@example.com" />
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
</style>
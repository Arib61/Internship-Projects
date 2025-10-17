<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();
const clientId = route.params.id;

const client = ref(null);
const gerant = ref(null);
const companyInfo = ref(null);
const loading = ref(true);
const error = ref(null);

// Format currency function
function formatCurrency(amount) {
  if (!amount) return '0 DH';
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('MAD', 'DH');
}

// Get badge class based on client type
function getTypeClientBadgeClass(type) {
  const typeMap = {
    'Premium': 'badge bg-warning text-dark',
    'VIP': 'badge bg-danger text-white',
    'Standard': 'badge bg-primary text-white',
    'Enterprise': 'badge bg-success text-white',
  };
  return typeMap[type] || 'badge bg-secondary text-white';
}

// Get status badge class
function getStatusBadgeClass(status) {
  const statusMap = {
    'ACTIVE': 'badge bg-success',
    'INACTIVE': 'badge bg-danger',
    'PROSPECT': 'badge bg-warning text-dark',
  };
  return statusMap[status] || 'badge bg-secondary';
}

// Get image URL for Laravel Storage
function getImageUrl(path) {
  if (!path) return null;
  return `http://localhost:8000/storage/${path}`;
}

// Check if file is PDF
function isPDF(filename) {
  if (!filename) return false;
  return filename.toLowerCase().includes('.pdf');
}

// Check if file is image
function isImage(filename) {
  if (!filename) return false;
  const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'];
  return imageExtensions.some(ext => filename.toLowerCase().includes(ext));
}

// Fetch company information
async function fetchCompanyInfo() {
  try {
    // Utiliser axios.get avec votre route API existante
    const response = await axios.get('/societes');
    
    // Log pour déboguer la structure de la réponse
    console.log("Réponse API société brute:", response);
    
    // Vérifier si la réponse contient des données
    if (response.data) {
      // Adapter les noms de propriétés si nécessaire
      // Supposons que l'API renvoie des noms différents, nous les mappons ici
      companyInfo.value = {
        nom: response.data.nom_francais,
        adresse: response.data.adresse_siege_social_francais,
        telephone: response.data.telephone,
        email: response.data.email,
        site_web: response.data.website || response.data.site_web || response.data.url,
        logo: response.data.logo || response.data.logo_path,
        rc: response.data.rc,
        identifiant_fiscal: response.data.identifiant_fiscal,
        tp: response.data.tp,
        ice: response.data.ice
      };
      
      console.log("Informations société traitées:", companyInfo.value);
    } else {
      console.error("La réponse de l'API ne contient pas de données");
      companyInfo.value = null;
    }
  } catch (err) {
    console.error("Erreur lors du chargement des informations de la société:", err);
    companyInfo.value = null;
  }
}

// Fetch client data
async function fetchClientData() {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await axios.get(`/clients/${clientId}`);
    client.value = response.data;
    
    // Récupérer les informations du gérant si un gérant est associé
    if (client.value.gerant_id) {
      await fetchGerantData(client.value.gerant_id);
    }
    
    // Récupérer les informations de la société
    await fetchCompanyInfo();
    
    document.title = `Client: ${client.value.nom_francais || 'Détails'}`;
  } catch (err) {
    console.error("Erreur lors du chargement des données client:", err);
    error.value = "Impossible de charger les informations du client. Veuillez réessayer plus tard.";
    
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: "Impossible de charger les informations du client.",
      confirmButtonText: "Retour à la liste",
    }).then(() => {
      router.push('/clients');
    });
  } finally {
    loading.value = false;
  }
}

// Fetch gerant data
async function fetchGerantData(gerantId) {
  try {
    const response = await axios.get(`/gerants/${gerantId}`);
    gerant.value = response.data;
  } catch (err) {
    console.error("Erreur lors du chargement des données du gérant:", err);
    gerant.value = null;
  }
}

// Format date
function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR');
}

// Print client details
function printClientDetails() {
  if (!client.value) return;
  
  // Créer le contenu HTML pour l'impression
  const printContent = `<!DOCTYPE html>
<html>
  <head>
    <title></title>
    <meta charset="UTF-8">
    <style>
      @page {
        size: auto;
        margin: 10mm;
      }
      
      @media print {
        html, body {
          -webkit-print-color-adjust: exact !important;
          print-color-adjust: exact !important;
        }
        
        body {
          margin: 0;
          padding: 0;
        }
        
        .no-print {
          display: none !important;
        }
        
        @page {
          margin: 0;
        }
        
        @page :first {
          margin-top: 0;
        }
        
        @page :left {
          margin-left: 0;
        }
        
        @page :right {
          margin-right: 0;
        }
      }
      
      body {
        font-family: Arial, sans-serif;
        margin: 20px;
        font-size: 12px;
        line-height: 1.4;
      }
      .header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 15px;
      }
      .company-logo {
        width: 80px;
        height: 80px;
        margin-right: 20px;
        border-radius: 8px;
        object-fit: contain;
      }
      .header-content {
        flex: 1;
      }
      .client-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
        text-align: left;
      }
      .print-date {
        font-size: 11px;
        color: #666;
        text-align: left;
      }
      .section {
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
      }
      .section-title {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 10px;
        background-color: #f5f5f5;
        padding: 8px;
        border-left: 4px solid #007bff;
      }
      .row {
        display: flex;
        margin-bottom: 8px;
      }
      .label {
        font-weight: bold;
        width: 200px;
        color: #333;
      }
      .value {
        flex: 1;
        color: #555;
      }
      .badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
      }
      .bg-success {
        background-color: #28a745;
        color: white;
      }
      .bg-danger {
        background-color: #dc3545;
        color: white;
      }
      .bg-warning {
        background-color: #ffc107;
        color: #212529;
      }
      .bg-primary {
        background-color: #007bff;
        color: white;
      }
      .bg-secondary {
        background-color: #6c757d;
        color: white;
      }
      .bg-info {
        background-color: #17a2b8;
        color: white;
      }
      .bg-light {
        background-color: #f8f9fa;
        color: #212529;
        border: 1px solid #dee2e6;
      }
      .footer {
        margin-top: 40px;
        padding-top: 20px;
      }
      .company-signature {
        text-align: center;
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #000;
        font-weight: normal;
        line-height: 1.4;
      }
      .no-print {
        margin-top: 30px;
        text-align: center;
      }
      .print-btn {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        margin: 0 5px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
      }
      .print-btn:hover {
        background-color: #0056b3;
      }
      .close-btn {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        margin: 0 5px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
      }
      .close-btn:hover {
        background-color: #545b62;
      }
    </style>
  </head>
  <body>
    <div class="header">
      ${companyInfo.value?.logo ? `<img src="${getImageUrl(companyInfo.value.logo)}" alt="Logo" class="company-logo" />` : '<div class="company-logo" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666;">LOGO</div>'}
      <div class="header-content">
        <div class="client-title">Détails du client: ${client.value.nom_francais}</div>
        <div class="print-date">Date d'impression: ${new Date().toLocaleDateString('fr-FR')} à ${new Date().toLocaleTimeString('fr-FR')}</div>
      </div>
    </div>
    
    <div class="section">
      <div class="section-title">Informations générales</div>
      <div class="row">
        <div class="label">Nom (Français):</div>
        <div class="value">${client.value.nom_francais || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Nom (Arabe):</div>
        <div class="value">${client.value.nom_arabe || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Type d'entreprise:</div>
        <div class="value">${client.value.type_entreprise || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Type de client:</div>
        <div class="value">${client.value.type_client || '-'}</div>
      </div>
    </div>
    
    <div class="section">
      <div class="section-title">Adresse</div>
      <div class="row">
        <div class="label">Adresse (Français):</div>
        <div class="value">${client.value.adresse_siege_social_francais || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Adresse (Arabe):</div>
        <div class="value">${client.value.adresse_siege_social_arabe || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Pays:</div>
        <div class="value">${client.value.pays || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Ville:</div>
        <div class="value">
          <span class="badge bg-light">${client.value.ville || '-'}</span>
        </div>
      </div>
    </div>
    
    <div class="section">
      <div class="section-title">Informations légales</div>
      <div class="row">
        <div class="label">ICE:</div>
        <div class="value">${client.value.ice || '-'}</div>
      </div>
      <div class="row">
        <div class="label">RC:</div>
        <div class="value">${client.value.rc || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Identifiant Fiscal:</div>
        <div class="value">${client.value.identifiant_fiscal || '-'}</div>
      </div>
      <div class="row">
        <div class="label">TP:</div>
        <div class="value">${client.value.tp || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Tribunal:</div>
        <div class="value">${client.value.tribunal || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Type Tribunal:</div>
        <div class="value">${client.value.type_tribunal || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Capital Social:</div>
        <div class="value">${formatCurrency(client.value.capital_social)}</div>
      </div>
    </div>
    
    <div class="section">
      <div class="section-title">Coordonnées</div>
      <div class="row">
        <div class="label">Téléphone:</div>
        <div class="value">${client.value.telephone || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Email:</div>
        <div class="value">${client.value.email || '-'}</div>
      </div>
      <div class="row">
        <div class="label">Site Web:</div>
        <div class="value">${client.value.website || '-'}</div>
      </div>
    </div>
    
    <div class="footer">
      <div class="company-signature">
        TEL: ${companyInfo.value?.telephone || 'N/A'} SIEGE SOCIAL: ${companyInfo.value?.adresse || 'N/A'}
        <br>
        RC: ${companyInfo.value?.rc || 'N/A'} - IF: ${companyInfo.value?.identifiant_fiscal || 'N/A'} - TP: ${companyInfo.value?.tp || 'N/A'} - ICE: ${companyInfo.value?.ice || 'N/A'}
      </div>
    </div>
    
    <div class="no-print">
      <button onclick="window.print()" class="print-btn">
        Imprimer
      </button>
      <button onclick="window.close()" class="close-btn">
        Fermer
      </button>
    </div>
    
    <script>
      window.onload = function() {
        document.title = "";
      }
    <\/script>
  </body>
</html>`;
  
  // Créer une nouvelle fenêtre d'impression
  const printWindow = window.open("", "_blank", "width=800,height=600");
  
  if (printWindow) {
    printWindow.document.open();
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Attendre que le contenu soit chargé
    printWindow.onload = function() {
      printWindow.focus();
      // Définir un titre vide pour éviter l'affichage de l'URL
      printWindow.document.title = "";
    };
  } else {
    // Fallback si la popup est bloquée
    Swal.fire({
      icon: "warning",
      title: "Popup bloquée",
      text: "Veuillez autoriser les popups pour imprimer le document.",
      confirmButtonText: "OK"
    });
  }
}

// Export client data to Excel
async function exportToExcel() {
  if (!client.value) return;
  
  try {
    const { utils, write } = await import('xlsx');
    const { saveAs } = await import('file-saver');
    
    const clientData = {
      "Nom (Français)": client.value.nom_francais || '',
      "Nom (Arabe)": client.value.nom_arabe || '',
      "Type d'entreprise": client.value.type_entreprise || '',
      "Type de client": client.value.type_client || '',
      "Gérant": gerant.value ? gerant.value.nom : '',
      "CIN Gérant": gerant.value ? gerant.value.cin : '',
      "Téléphone Gérant": gerant.value ? gerant.value.telephone : '',
      "Email Gérant": gerant.value ? gerant.value.email : '',
      "Adresse (Français)": client.value.adresse_siege_social_francais || '',
      "Adresse (Arabe)": client.value.adresse_siege_social_arabe || '',
      "Pays": client.value.pays || '',
      "Ville": client.value.ville || '',
      "ICE": client.value.ice || '',
      "RC": client.value.rc || '',
      "Identifiant Fiscal": client.value.identifiant_fiscal || '',
      "TP": client.value.tp || '',
      "Tribunal": client.value.tribunal || '',
      "Type Tribunal": client.value.type_tribunal || '',
      "Capital Social": client.value.capital_social || '',
      "Téléphone": client.value.telephone || '',
      "Email": client.value.email || '',
      "Site Web": client.value.website || '',
    };
    
    const worksheet = utils.json_to_sheet([clientData]);
    const workbook = utils.book_new();
    utils.book_append_sheet(workbook, worksheet, "Client");
    
    const excelBuffer = write(workbook, { bookType: 'xlsx', type: 'array' });
    const data = new Blob([excelBuffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    
    saveAs(data, `client_${client.value.nom_francais}_${new Date().toISOString().split("T")[0]}.xlsx`);
    
    Swal.fire({
      icon: "success",
      title: "Succès",
      text: "Le fichier Excel a été généré avec succès",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 2000,
    });
  } catch (error) {
    console.error("Erreur lors de l'export Excel:", error);
    Swal.fire({
      icon: "error",
      title: "Erreur",
      text: "Impossible de générer le fichier Excel. Veuillez réessayer.",
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 2000,
    });
  }
}

// Navigate to edit form
function editClient() {
  router.push(`/admin/clients/${clientId}/edit`);
}

// Fetch client data on component mount
onMounted(() => {
  fetchClientData();
});
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
      
      <!-- Error state -->
      <div v-else-if="error" class="alert alert-danger m-4" role="alert">
        <h4 class="alert-heading">Erreur!</h4>
        <p>{{ error }}</p>
        <hr>
        <div class="d-flex justify-content-end">
          <button @click="router.push('/clients')" class="btn btn-outline-danger">
            Retour à la liste
          </button>
        </div>
      </div>
      
      <!-- Client details -->
      <div v-else-if="client" class="client-details">
        <!-- Header with actions -->
        <div class="page-header settings-pg-header">
          <div class="add-item d-flex justify-content-between align-items-center w-100">
            <div class="page-title">
              <div class="client-details-title">
                <div class="title-icon">
                  <i data-feather="user" class="feather-user"></i>
                </div>
                <div class="title-content">
                  <h4 class="main-title">Détails du client</h4>
                  <h6 class="client-name">{{ client.nom_francais }}</h6>
                </div>
              </div>
            </div>
            
            <div class="page-actions d-flex align-items-center justify-content-between w-auto">
              <!-- Export Actions (groupés à gauche) -->
              <ul class="table-top-head me-4">
                <li>
                  <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" title="Imprimer"
                    @click="printClientDetails">
                    <i data-feather="printer" class="feather-printer"></i>
                  </a>
                </li>
              </ul>
              
              <!-- Back Button (séparé à droite) -->
              <button 
                type="button"
                class="btn btn-back-custom"
                @click="router.push(`/admin/clients`)"
                data-bs-toggle="tooltip" 
                data-bs-placement="top" 
                title="Retour à la liste des clients"
              >
                <i data-feather="arrow-left" class="feather-arrow-left me-2"></i>
                Retour
              </button>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-xl-12">
            <!-- Section: Informations générales -->
            <div class="card mb-4">
              <div class="card-header d-flex align-items-center">
                <i data-feather="info" class="feather-info me-2"></i>
                <h5 class="mb-0">Informations générales</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Type d'entreprise</label>
                    <div class="form-control-static">{{ client.type_entreprise || '-' }}</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Type de client</label>
                    <div class="form-control-static">{{ client.type_client || '-' }}</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom Français</label>
                    <div class="form-control-static">{{ client.nom_francais || '-' }}</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom Arabe</label>
                    <div class="form-control-static" style="direction: rtl">{{ client.nom_arabe || '-' }}</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Statut</label>
                    <div class="form-control-static">
                      <span :class="getStatusBadgeClass(client.status)">
                        {{ client.status }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Section: Informations du gérant -->
            <div v-if="gerant" class="card mb-4">
              <div class="card-header d-flex align-items-center">
                <i data-feather="user" class="feather-user me-2"></i>
                <h5 class="mb-0">Informations du gérant</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom</label>
                    <div class="form-control-static">
                      <span class="badge bg-info text-white me-2">
                        <i data-feather="user" class="feather-user" style="width: 14px; height: 14px;"></i>
                      </span>
                      {{ gerant.nom || '-' }}
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">CIN</label>
                    <div class="form-control-static">{{ gerant.cin || '-' }}</div>
                  </div>
                  <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold">Adresse</label>
                    <div class="form-control-static">{{ gerant.address || '-' }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Date de naissance</label>
                    <div class="form-control-static">{{ formatDate(gerant.date_naissance) }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Téléphone</label>
                    <div class="form-control-static">
                      <a v-if="gerant.telephone" :href="`tel:${gerant.telephone}`" class="text-success">
                        {{ gerant.telephone }}
                      </a>
                      <span v-else>-</span>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <div class="form-control-static">
                      <a v-if="gerant.email" :href="`mailto:${gerant.email}`" class="text-primary">
                        {{ gerant.email }}
                      </a>
                      <span v-else>-</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Message si aucun gérant -->
            <div v-else-if="!gerant && client.gerant_id" class="card mb-4">
              <div class="card-header d-flex align-items-center">
                <i data-feather="user" class="feather-user me-2"></i>
                <h5 class="mb-0">Informations du gérant</h5>
              </div>
              <div class="card-body">
                <div class="alert alert-warning" role="alert">
                  <i data-feather="alert-triangle" class="feather-alert-triangle me-2"></i>
                  Impossible de charger les informations du gérant associé.
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
                    <label class="form-label fw-bold">Adresse Siège Social (FR)</label>
                    <div class="form-control-static">{{ client.adresse_siege_social_francais || '-' }}</div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Adresse Siège Social (AR)</label>
                    <div class="form-control-static" style="direction: rtl">{{ client.adresse_siege_social_arabe || '-' }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Pays</label>
                    <div class="form-control-static">{{ client.pays || '-' }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Ville</label>
                    <div class="form-control-static">
                      <span class="badge bg-light text-dark">{{ client.ville || '-' }}</span>
                    </div>
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
                  <!-- CIN -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">CIN</label>
                    <div v-if="client.cin" class="mt-2">
                      <div class="border rounded p-3 document-container">
                        <div class="mb-2">
                          <span class="fw-bold">Document CIN</span>
                        </div>
                        
                        <!-- PDF Preview -->
                        <div v-if="isPDF(client.cin)" class="pdf-preview">
                          <iframe 
                            :src="getImageUrl(client.cin)"
                            width="100%"
                            height="300px"
                            frameborder="0"
                          ></iframe>
                        </div>
                        
                        <!-- Image Preview -->
                        <div v-else-if="isImage(client.cin)" class="image-preview">
                          <img
                            :src="getImageUrl(client.cin)"
                            alt="CIN"
                            class="document-image"
                            @error="handleImageError"
                          />
                        </div>
                        
                        <!-- Fallback for other file types -->
                        <div v-else class="file-preview">
                          <div class="text-center p-4">
                            <i data-feather="file" class="feather-file mb-2" style="width: 48px; height: 48px;"></i>
                            <p class="mb-0">{{ client.cin }}</p>
                            <a :href="getImageUrl(client.cin)" target="_blank" class="btn btn-sm btn-primary mt-2">
                              Télécharger
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="form-control-static text-muted">
                      Aucun document CIN disponible
                    </div>
                  </div>

                  <!-- Certificat Négatif -->
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Certificat Négatif</label>
                    <div v-if="client.certificat_negative" class="mt-2">
                      <div class="border rounded p-3 document-container">
                        <div class="mb-2">
                          <span class="fw-bold">Certificat Négatif</span>
                        </div>
                        
                        <!-- PDF Preview -->
                        <div v-if="isPDF(client.certificat_negative)" class="pdf-preview">
                          <iframe 
                            :src="getImageUrl(client.certificat_negative)"
                            width="100%"
                            height="300px"
                            frameborder="0"
                          ></iframe>
                        </div>
                        
                        <!-- Image Preview -->
                        <div v-else-if="isImage(client.certificat_negative)" class="image-preview">
                          <img
                            :src="getImageUrl(client.certificat_negative)"
                            alt="Certificat Négatif"
                            class="document-image"
                            @error="handleImageError"
                          />
                        </div>
                        
                        <!-- Fallback for other file types -->
                        <div v-else class="file-preview">
                          <div class="text-center p-4">
                            <i data-feather="file" class="feather-file mb-2" style="width: 48px; height: 48px;"></i>
                            <p class="mb-0">{{ client.certificat_negative }}</p>
                            <a :href="getImageUrl(client.certificat_negative)" target="_blank" class="btn btn-sm btn-primary mt-2">
                              Télécharger
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else class="form-control-static text-muted">
                      Aucun certificat négatif disponible
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
                    <label class="form-label fw-bold">Capital Social</label>
                    <div class="form-control-static">
                      <span class="fw-semibold text-success">{{ formatCurrency(client.capital_social) }}</span>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">ICE</label>
                    <div class="form-control-static">{{ client.ice || '-' }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Registre de Commerce (RC)</label>
                    <div class="form-control-static">{{ client.rc || '-' }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Identifiant Fiscal</label>
                    <div class="form-control-static">{{ client.identifiant_fiscal || '-' }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Taxe Professionnelle (TP)</label>
                    <div class="form-control-static">{{ client.tp || '-' }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Tribunal</label>
                    <div class="form-control-static">{{ client.tribunal || '-' }}</div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Type Tribunal</label>
                    <div class="form-control-static">{{ client.type_tribunal || '-' }}</div>
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
                    <label class="form-label fw-bold">Téléphone</label>
                    <div class="form-control-static">
                      <a v-if="client.telephone" :href="`tel:${client.telephone}`" class="text-success">
                        {{ client.telephone }}
                      </a>
                      <span v-else>-</span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <div class="form-control-static">
                      <a v-if="client.email" :href="`mailto:${client.email}`" class="text-primary">
                        {{ client.email }}
                      </a>
                      <span v-else>-</span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Site web</label>
                    <div class="form-control-static">
                      <a v-if="client.website" :href="client.website" target="_blank" class="text-info">
                        {{ client.website }}
                      </a>
                      <span v-else>-</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
   
  </div>
</template>

<style scoped>
/* Styles pour le titre des détails du client */
.client-details-title {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 0;
}

.title-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #ABC270 0%, #9bb05f 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(171, 194, 112, 0.3);
  transition: all 0.3s ease;
  position: relative; /* Ajouté pour un meilleur contrôle */
}

.title-icon:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(171, 194, 112, 0.4);
}

.title-icon i {
  color: white;
  width: 28px;
  height: 28px;
  display: flex; /* Ajouté pour centrer l'icône */
  align-items: center; /* Ajouté pour centrer l'icône */
  justify-content: center; /* Ajouté pour centrer l'icône */
  position: absolute; /* Ajouté pour un centrage parfait */
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* Centrage parfait */

}

.title-content {
  flex: 1;
}

.main-title {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.client-name {
  font-size: 1.2rem;
  font-weight: 500;
  color: #ABC270;
  margin: 0.25rem 0 0 0;
  text-transform: capitalize;
}

/* Bouton retour personnalisé */
.btn-back-custom {
  background: linear-gradient(135deg, #ABC270 0%, #9bb05f 100%);
  border: none;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(171, 194, 112, 0.3);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-back-custom:hover {
  background: linear-gradient(135deg, #9bb05f 0%, #8a9e54 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(171, 194, 112, 0.4);
  color: white;
}

.btn-back-custom:active {
  transform: translateY(0);
  box-shadow: 0 2px 6px rgba(171, 194, 112, 0.3);
}

.btn-back-custom i {
  width: 18px;
  height: 18px;
}

/* Responsive pour le titre */
@media (max-width: 768px) {
  .client-details-title {
    flex-direction: column;
    text-align: center;
    gap: 0.75rem;
  }
  
  .title-icon {
    width: 50px;
    height: 50px;
    align-self: center;
  }
  
  .title-icon i {
    width: 24px;
    height: 24px;
  }
  
  .main-title {
    font-size: 1.5rem;
  }
  
  .client-name {
    font-size: 1rem;
  }
  
  .btn-back-custom {
    padding: 0.6rem 1.2rem;
    font-size: 0.9rem;
  }
}

.card {
  border: 1px solid #e0e0e0;
  border-radius: 0.25rem;
  margin-bottom: 1.5rem;
}

.card-header {
  background-color: #f8f9fa;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.card-body {
  padding: 1rem;
}

.form-control-static {
  padding: 0.375rem 0;
  margin-bottom: 0;
  font-size: 1rem;
  line-height: 1.5;
  color: #495057;
  min-height: 24px;
}

.form-label {
  margin-bottom: 0.25rem;
  color: #6c757d;
}

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
  background-color: #28a745 !important;
}

.bg-danger {
  background-color: #dc3545 !important;
}

.bg-warning {
  background-color: #ffc107 !important;
  color: #212529 !important;
}

.bg-primary {
  background-color: #007bff !important;
}

.bg-secondary {
  background-color: #6c757d !important;
}

.bg-info {
  background-color: #17a2b8 !important;
}

.bg-light {
  background-color: #f8f9fa !important;
  color: #212529 !important;
}

.text-success {
  color: #28a745 !important;
}

.text-primary {
  color: #007bff !important;
}

.text-info {
  color: #17a2b8 !important;
}

.text-muted {
  color: #6c757d !important;
}

.fw-bold {
  font-weight: 600 !important;
}

.fw-semibold {
  font-weight: 500 !important;
}

.me-1 {
  margin-right: 0.25rem !important;
}

.me-2 {
  margin-right: 0.5rem !important;
}

.me-4 {
  margin-right: 1.5rem !important;
}

.mb-2 {
  margin-bottom: 0.5rem !important;
}

.mb-3 {
  margin-bottom: 1rem !important;
}

.mb-4 {
  margin-bottom: 1.5rem !important;
}

.mt-2 {
  margin-top: 0.5rem !important;
}

.mt-3 {
  margin-top: 1rem !important;
}

.mt-4 {
  margin-top: 1.5rem !important;
}

.ms-2 {
  margin-left: 0.5rem !important;
}

.p-2 {
  padding: 0.5rem !important;
}

.p-3 {
  padding: 1rem !important;
}

.p-4 {
  padding: 1.5rem !important;
}

.p-5 {
  padding: 3rem !important;
}

.border {
  border: 1px solid #dee2e6 !important;
}

.rounded {
  border-radius: 0.25rem !important;
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

.table-top-head {
  display: flex;
  align-items: center;
  list-style: none;
  padding: 0;
  margin: 0;
}

.table-top-head li {
  margin-left: 10px;
}

.table-top-head li a {
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  border-radius: 5px;
  color: #6c757d;
  transition: all 0.2s ease;
}

.table-top-head li a:hover {
  background-color: #e9ecef;
  color: #495057;
}

.table-top-head li a img {
  width: 20px;
  height: 20px;
}

.document-container {
  max-width: 400px;
  background-color: #f8f9fa;
}

.pdf-preview {
  border: 1px solid #dee2e6;
  border-radius: 4px;
  overflow: hidden;
  background-color: #fff;
}

.image-preview {
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #fff;
  min-height: 200px;
  border-radius: 4px;
  border:1px solid #dee2e6;
}

.document-image {
  max-height: 300px;
  max-width: 100%;
  object-fit: contain;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.file-preview {
  background-color: #fff;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  color: #6c757d;
}

/* Company Header Styles */
.company-header {
  display: flex;
  align-items: center;
  padding: 1rem 2rem;
  background-color: #f8f9fa;
  border-bottom: 1px solid #e0e0e0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.company-logo-container {
  width: 60px;
  height: 60px;
  margin-right: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.company-logo-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 8px;
}

.company-logo-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #ABC270 0%, #9bb05f 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.8rem;
  font-weight: bold;
}

.company-name {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2c3e50;
}

/* Company Footer Styles */
.company-footer {
  margin-top: 2rem;
  padding: 1.5rem 2rem;
  background-color: #f8f9fa;
  border-top: 1px solid #e0e0e0;
}

.company-footer-content {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.company-footer-logo {
  width: 50px;
  height: 50px;
  margin-right: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.company-footer-logo-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 6px;
}

.company-footer-logo-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #ABC270 0%, #9bb05f 100%);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  font-weight: bold;
}

.company-footer-info {
  flex: 1;
}

.company-footer-row {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 0.5rem;
}

.company-footer-item {
  display: flex;
  align-items: center;
  margin-right: 1.5rem;
  margin-bottom: 0.5rem;
  color: #495057;
  font-size: 0.9rem;
}

.company-footer-icon {
  width: 16px;
  height: 16px;
  margin-right: 0.5rem;
  color: #ABC270;
}

.company-footer-copyright {
  text-align: center;
  font-size: 0.85rem;
  color: #6c757d;
  padding-top: 1rem;
  border-top: 1px solid #e0e0e0;
}

/* Responsive styles */
@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .page-actions {
    margin-top: 1rem;
    align-self: flex-end;
    flex-direction: column;
    align-items: flex-end;
  }
  
  .page-actions .btn {
    margin-bottom: 0.5rem;
  }
  
  .company-header {
    padding: 0.75rem 1rem;
  }
  
  .company-logo-container {
    width: 40px;
    height: 40px;
  }
  
  .company-name {
    font-size: 1.2rem;
  }
  
  .company-footer-content {
    flex-direction: column;
    text-align: center;
  }
  
  .company-footer-logo {
    margin-right: 0;
    margin-bottom: 1rem;
  }
  
  .company-footer-row {
    justify-content: center;
  }
}
</style>
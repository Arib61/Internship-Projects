<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <div class="page-header">
        <div class="add-item d-flex">
          <div class="page-title">
            <h4>Gestion des Bureaux d'Équipe</h4>
            <h6>Gérez vos bureaux d'équipe</h6>
          </div>
        </div>
        <ul class="table-top-head">
          <li>
            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" title="Exporter en PDF"
              @click="exportToPDF">
              <img src="@/assets/img/icons/pdf.svg" alt="PDF" />
            </a>
          </li>
          <li>
            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" title="Exporter en Excel"
              @click="exportToExcel">
              <img src="@/assets/img/icons/excel.svg" alt="Excel" />
            </a>
          </li>
        </ul>
        <div class="page-btn">
          <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-bureau"
            @click="resetForm">
            <VueFeather type="plus-circle" class="me-2" />
            Ajouter Nouveau Bureau
          </a>
        </div>
      </div>

      <div class="card table-list-card">
        <div class="card-body">
          <div class="table-top">
            <div class="search-and-filters d-flex align-items-center gap-3">
              <!-- Barre de recherche -->
              <div class="search-set">
                <div class="search-input">
                  <input type="text" class="dark-input" placeholder="Rechercher..." v-model="search"
                    @input="filterBureaux" />
                  <a href="" class="btn btn-searchset">
                    <VueFeather type="search" />
                  </a>
                </div>
              </div>
              
              <!-- Filtres à côté -->
              <div class="filters-inline d-flex align-items-center gap-2">
                <div class="filter-group-inline">
                  <select v-model="situationFilter" class="form-select form-select-sm" @change="filterBureaux">
                    <option value="">Situation</option>
                    <option value="DEMANDE">Demande</option>
                    <option value="EN_COURS">En cours</option>
                    <option value="REJETTE">Rejeté</option>
                  </select>
                </div>
                <div class="filter-group-inline">
                  <select v-model="paymentFilter" class="form-select form-select-sm" @change="filterBureaux">
                    <option value="">Paiement</option>
                    <option value="paye">Payé</option>
                    <option value="impaye">Impayé</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Table Section -->
          <div class="table-responsive">
            <a-table class="table datanew" :columns="columns" :data-source="filteredData" :row-selection="{}"
              :pagination="false">
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'client'">
                  <div class="text-nowrap">
                    {{ record.client_nom }}
                  </div>
                </template>
                <template v-else-if="column.key === 'created_by'">
                  <div class="text-nowrap">
                    {{ record.created_by_nom }}
                  </div>
                </template>
                <template v-else-if="column.key === 'dates'">
                  <div class="text-nowrap">
                    {{ formatDate(record.date_debut) }} - {{ formatDate(record.date_fin) }}
                  </div>
                </template>
                <template v-else-if="column.key === 'montant'">
                  <div class="text-nowrap">
                    {{ formatMontant(record.montant) }}
                  </div>
                </template>
                <template v-else-if="column.key === 'situation'">
                  <div class="text-nowrap">
                    <span :class="getSituationBadgeClass(record.situation)">
                      {{ formatSituation(record.situation) }}
                    </span>
                  </div>
                </template>
                <template v-else-if="column.key === 'payement'">
                  <div class="text-nowrap">
                    <span :class="record.payement ? 'badge-paye' : 'badge-impaye'">
                      {{ record.payement ? 'Payé' : 'Impayé' }}
                    </span>
                  </div>
                </template>
                <template v-else-if="column.key === 'reference'">
                  <div class="text-nowrap">
                    {{ record.reference_numero || '-' }}
                  </div>
                </template>
                <template v-else-if="column.key === 'action'">
                  <td class="action-table-data">
                    <div class="edit-delete-action">
                      <a class="me-2 p-2 mb-0 action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#add-bureau"
                        title="Modifier" @click.prevent="onEditBureau(record)">
                        <i data-feather="edit" class="feather-edit"></i>
                      </a>
                      <a class="me-2 confirm-text p-2 mb-0 action-btn delete-btn" href="javascript:void(0);"
                        data-bs-toggle="tooltip" title="Supprimer" @click.prevent="onDeleteBureau(record)">
                        <i data-feather="trash-2" class="feather-trash-2"></i>
                      </a>
                    </div>
                  </td>
                </template>
              </template>
            </a-table>
          </div>

          <!-- Pagination -->
          <div class="custom-pagination-wrapper d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <div class="d-flex align-items-center">
              <label class="me-2 fw-semibold">Afficher :</label>
              <select v-model="pageSize" class="form-select form-select-sm" style="width: auto; min-width: 80px;"
                @change="onPageSizeChange">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span class="ms-2 text-muted">entrées par page</span>
            </div>

            <div class="pagination-info text-muted">
              Affichage de {{ startRecord }} à {{ endRecord }} sur {{ allFilteredData.length }} entrées
            </div>

            <div class="pagination-controls d-flex align-items-center gap-3">
              <div class="pagination-nav d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm pagination-btn" :disabled="currentPage === 1"
                  @click="goToPage(1)" title="Première page">
                  <VueFeather type="chevrons-left" size="14" />
                </button>
                <button class="btn btn-outline-secondary btn-sm pagination-btn" :disabled="currentPage === 1"
                  @click="goToPage(currentPage - 1)" title="Page précédente">
                  <VueFeather type="chevron-left" size="14" />
                </button>

                <div class="page-selector d-flex align-items-center gap-2">
                  <span class="text-muted small">Page :</span>
                  <select v-model="currentPage" class="form-select form-select-sm page-select" @change="onPageChange">
                    <option v-for="page in totalPages" :key="page" :value="page">
                      {{ page }}
                    </option>
                  </select>
                  <span class="text-muted small">sur {{ totalPages }}</span>
                </div>

                <button class="btn btn-outline-secondary btn-sm pagination-btn" :disabled="currentPage === totalPages"
                  @click="goToPage(currentPage + 1)" title="Page suivante">
                  <VueFeather type="chevron-right" size="14" />
                </button>
                <button class="btn btn-outline-secondary btn-sm pagination-btn" :disabled="currentPage === totalPages"
                  @click="goToPage(totalPages)" title="Dernière page">
                  <VueFeather type="chevrons-right" size="14" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Ajout/Édition -->
  <div class="modal fade" id="add-bureau" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ isEditing ? 'Modifier Bureau' : 'Ajouter Nouveau Bureau' }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
            @click="resetForm"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="row">
              <!-- Client -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Client</label>
                <div class="input-group">
                  <select v-model="formData.client_id" class="form-select" required>
                    <option value="" disabled>Sélectionner un client</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">
                      {{ client.nom_francais }}
                    </option>
                  </select>
                  <button type="button" class="btn btn-primary" @click="redirectToAddClient">
                    <VueFeather type="plus" size="16" />
                  </button>
                </div>
              </div>

              <!-- Gestionnaire -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Gestionnaire</label>
                <div class="input-group">
                  <select v-model="formData.gestionnaire_id" class="form-select" required>
                    <option value="" disabled>Sélectionner un gestionnaire</option>
                    <option v-for="gestionnaire in gestionnaires" :key="gestionnaire.id" :value="gestionnaire.id">
                      {{ gestionnaire.nom }} {{ gestionnaire.prenom }}
                    </option>
                  </select>
                  <button type="button" class="btn btn-primary" @click="openAddGestionnaireModal">
                    <VueFeather type="plus" size="16" />
                  </button>
                </div>
              </div>

              <!-- Date de début -->
              <div class="col-md-4 mb-3">
                <label class="form-label">Date de début</label>
                <input type="date" class="form-control" v-model="formData.date_debut" @change="calculateEndDate"
                  required />
              </div>

              <!-- Durée -->
              <div class="col-md-4 mb-3">
                <label class="form-label">Durée</label>
                <select class="form-select" v-model="formData.duree_mois" @change="calculateEndDate" required>
                  <option value="" disabled>Sélectionner une durée</option>
                  <option value="3">3 mois</option>
                  <option value="6">6 mois (demi-année)</option>
                  <option value="12">1 an</option>
                  <option value="15">1 an et 3 mois</option>
                  <option value="18">1 an et demi</option>
                  <option value="24">2 ans</option>
                </select>
              </div>

              <!-- Date de fin -->
              <div class="col-md-4 mb-3">
                <label class="form-label">Date de fin</label>
                <input type="date" class="form-control" v-model="formData.date_fin" readonly />
              </div>

              <!-- Montant -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Montant</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" v-model="formData.montant" required />
                  <span class="input-group-text">MAD</span>
                </div>
              </div>

              <!-- Situation -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Situation</label>
                <select class="form-select" v-model="formData.situation" required>
                  <option value="DEMANDE">Demande</option>
                  <option value="EN_COURS">En cours</option>
                  <option value="REJETTE">Rejeté</option>
                </select>
              </div>

              <!-- Paiement -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Paiement</label>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" v-model="formData.payement" id="payementSwitch" />
                  <label class="form-check-label" for="payementSwitch">
                    {{ formData.payement ? "Payé" : "Non payé" }}
                  </label>
                </div>
              </div>

              <!-- Référence -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Numéro de référence</label>
                <input type="text" class="form-control" v-model="formData.reference_numero" maxlength="50" readonly />
                <small class="text-muted">Généré automatiquement</small>
              </div>

              <!-- Notes -->
              <div class="col-12 mb-3">
                <label class="form-label">Notes</label>
                <textarea class="form-control" v-model="formData.notes" rows="3"></textarea>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                {{ isSubmitting ? (isEditing ? 'Mise à jour...' : 'Création en cours...') : (isEditing ? 'Mettre à jour'
                  :
                  'Enregistrer') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Intégration du modal utilisateur -->
  <users-modal ref="usersModal" @user-added="onUserAdded" @user-edited="fetchGestionnaires"></users-modal>
</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import Swal from "sweetalert2";
import moment from 'moment';
import { Modal } from "bootstrap";
import VueFeather from 'vue-feather';

// Colonnes sans gestionnaire
const columns = [
  {
    title: "Client",
    dataIndex: "client_nom",
    key: "client",
    sorter: {
      compare: (a, b) => a.client_nom.localeCompare(b.client_nom),
    },
  },
  {
    title: "Créé par",
    dataIndex: "created_by_nom",
    key: "created_by",
    sorter: {
      compare: (a, b) => a.created_by_nom.localeCompare(b.created_by_nom),
    },
  },
  {
    title: "Période",
    key: "dates",
    sorter: {
      compare: (a, b) => new Date(a.date_debut) - new Date(b.date_debut),
    },
  },
  {
    title: "Montant",
    key: "montant",
    sorter: {
      compare: (a, b) => a.montant - b.montant,
    },
  },
  {
    title: "Situation",
    key: "situation",
    sorter: {
      compare: (a, b) => a.situation.localeCompare(b.situation),
    },
  },
  {
    title: "Paiement",
    key: "payement",
    sorter: {
      compare: (a, b) => a.payement - b.payement,
    },
  },
  {
    title: "Référence",
    key: "reference",
    sorter: {
      compare: (a, b) => (a.reference_numero || '').localeCompare(b.reference_numero || ''),
    },
  },
  {
    title: "Action",
    key: "action",
    sorter: false,
  },
];

export default {
  components: {
    VueFeather
  },
  data() {
    return {
      columns,
      allBureaux: [],
      clients: [],
      gestionnaires: [],
      users: [],
      companyInfo: null,
      search: "",
      situationFilter: "",
      paymentFilter: "",
      pageSize: 10,
      currentPage: 1,
      isSubmitting: false,
      formData: {
        id: null,
        client_id: '',
        gestionnaire_id: '',
        date_debut: '',
        date_fin: '',
        duree_mois: '',
        montant: 0,
        situation: 'DEMANDE',
        payement: false,
        reference_numero: '',
        notes: ''
      },
      isEditing: false
    };
  },
  computed: {
    filteredData() {
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return this.allFilteredData.slice(start, end);
    },

    allFilteredData() {
      let filtered = [...this.allBureaux];

      // Filtre par recherche textuelle
      const searchTerm = this.search.toLowerCase().trim();
      if (searchTerm) {
        filtered = filtered.filter(
          b =>
            (b.client_nom && b.client_nom.toLowerCase().includes(searchTerm)) ||
            (b.gestionnaire_nom && b.gestionnaire_nom.toLowerCase().includes(searchTerm)) ||
            (b.reference_numero && b.reference_numero.toLowerCase().includes(searchTerm)) ||
            (b.situation && b.situation.toLowerCase().includes(searchTerm))
        );
      }

      // Filtre par situation
      if (this.situationFilter) {
        filtered = filtered.filter(b => b.situation === this.situationFilter);
      }

      // Filtre de paiement
      if (this.paymentFilter) {
        if (this.paymentFilter === 'paye') {
          filtered = filtered.filter(b => b.payement === true);
        } else if (this.paymentFilter === 'impaye') {
          filtered = filtered.filter(b => b.payement === false);
        }
      }

      return filtered;
    },

    totalPages() {
      return Math.ceil(this.allFilteredData.length / this.pageSize);
    },

    startRecord() {
      if (this.allFilteredData.length === 0) return 0;
      return (this.currentPage - 1) * this.pageSize + 1;
    },

    endRecord() {
      const end = this.currentPage * this.pageSize;
      return Math.min(end, this.allFilteredData.length);
    },
  },
  methods: {
    // Générer une référence automatique
    generateReference() {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      const hours = String(now.getHours()).padStart(2, '0');
      const minutes = String(now.getMinutes()).padStart(2, '0');
      const seconds = String(now.getSeconds()).padStart(2, '0');
      const milliseconds = String(now.getMilliseconds()).padStart(3, '0');

      return `BUR-${year}${month}${day}-${hours}${minutes}${seconds}${milliseconds}`;
    },

    // Fetch company information
    async fetchCompanyInfo() {
      try {
        const response = await axios.get('/societes');
        
        if (response.data) {
          this.companyInfo = {
          
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
          
          console.log("Informations société traitées:", this.companyInfo);
        } else {
          console.error("La réponse de l'API ne contient pas de données");
          this.companyInfo = null;
        }
      } catch (err) {
        console.error("Erreur lors du chargement des informations de la société:", err);
        this.companyInfo = null;
      }
    },

    // Get image URL for Laravel Storage
    getImageUrl(path) {
      if (!path) return null;
      return `http://localhost:8000/storage/${path}`;
    },

    formatSituation(situation) {
      const situationMap = {
        'DEMANDE': 'Demande',
        'EN_COURS': 'En cours',
        'REJETTE': 'Rejeté'
      };
      return situationMap[situation] || situation;
    },

    getSituationBadgeClass(situation) {
      const badgeClasses = {
        'DEMANDE': 'badge bg-warning text-dark',
        'EN_COURS': 'badge bg-success',
        'REJETTE': 'badge bg-danger'
      };
      return badgeClasses[situation] || 'badge bg-secondary';
    },

    // Calculer la date de fin basée sur la date de début et la durée
    calculateEndDate() {
      if (this.formData.date_debut && this.formData.duree_mois) {
        const startDate = new Date(this.formData.date_debut);
        const endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + parseInt(this.formData.duree_mois));
        this.formData.date_fin = endDate.toISOString().split("T")[0];
      }
    },

    // Formater le montant
    formatMontant(montant) {
      return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "MAD",
      }).format(montant);
    },

    getSituationClass(situation) {
      return 'text-nowrap';
    },

    formatDate(date) {
      return moment(date).format('DD/MM/YYYY');
    },

    resetForm() {
      this.formData = {
        id: null,
        client_id: '',
        gestionnaire_id: '',
        date_debut: '',
        date_fin: '',
        duree_mois: '',
        montant: 0,
        situation: 'DEMANDE',
        payement: false,
        reference_numero: this.generateReference(),
        notes: ''
      };
      this.isEditing = false;
      this.isSubmitting = false;
    },

    async submitForm() {
      try {
        this.isSubmitting = true;
        const endpoint = this.isEditing
          ? `/bureaux-equipe/${this.formData.id}`
          : '/bureaux-equipe';

        const method = this.isEditing ? 'put' : 'post';

        // Ajouter l'utilisateur connecté comme created_by
        const user = JSON.parse(localStorage.getItem('user'));
        const payload = {
          ...this.formData,
          created_by: user.id
        };

        await axios[method](endpoint, payload);

        this.fetchBureaux();
        Swal.fire({
          icon: "success",
          title: "Succès",
          text: `Bureau ${this.isEditing ? 'modifié' : 'créé'} avec succès`,
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 2000,
        });

        // Fermer le modal
        this.resetForm();
        document.getElementById('add-bureau').querySelector('.btn-close').click();

      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text: error.response?.data?.message || "Une erreur est survenue",
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 3000,
        });
      } finally {
        this.isSubmitting = false;
      }
    },

    onPageSizeChange() {
      this.currentPage = 1;
    },

    onPageChange() {
      // La page est automatiquement mise à jour via v-model
    },

    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },

    // Export Excel synchronisé avec PDF (sans gestionnaire)
    exportToExcel() {
      const dataToExport = this.allFilteredData.map((bureau) => ({
        Client: bureau.client_nom,
        'Créé par': bureau.created_by_nom,
        Période: `${this.formatDate(bureau.date_debut)} - ${this.formatDate(bureau.date_fin)}`,
        Montant: this.formatMontant(bureau.montant),
        Situation: this.formatSituation(bureau.situation),
        Paiement: bureau.payement ? 'Payé' : 'Impayé',
        Référence: bureau.reference_numero || '-'
      }));

      const worksheet = XLSX.utils.json_to_sheet(dataToExport);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "BureauxEquipe");

      const excelBuffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });
      const data = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      saveAs(
        data,
        `bureaux_equipe_${new Date().toISOString().split("T")[0]}.xlsx`
      );
    },

    // Export PDF avec style d'impression simplifié
    exportToPDF() {
      if (!this.companyInfo) {
        this.generatePDFWithoutLogo();
        return;
      }

      // Créer un blob avec le contenu HTML pour éviter about:blank
      const pdfContent = this.generatePrintableHTML();
      const blob = new Blob([pdfContent], { type: 'text/html' });
      const url = URL.createObjectURL(blob);
      
      // Ouvrir dans une nouvelle fenêtre avec un titre personnalisé
      const printWindow = window.open(url, 'print_window', 'width=800,height=600,scrollbars=yes,resizable=yes');
      
      if (!printWindow) {
        Swal.fire({
          icon: "warning",
          title: "Popup bloquée",
          text: "Veuillez autoriser les popups pour générer le PDF.",
          confirmButtonText: "OK"
        });
        return;
      }

      // Nettoyer l'URL après utilisation
      printWindow.onbeforeunload = function() {
        URL.revokeObjectURL(url);
      };
      
      // Attendre que le contenu soit chargé puis définir le titre
      printWindow.onload = function() {
        printWindow.document.title = "Liste des bureaux d'équipe";
        printWindow.focus();
      };
    },

    // Générer le HTML pour l'impression sans gestionnaire
    generatePrintableHTML() {
      const currentDate = new Date();
      const formattedDate = currentDate.toLocaleDateString('fr-FR');
      const formattedTime = currentDate.toLocaleTimeString('fr-FR');
      
      return `<!DOCTYPE html>
<html>
  <head>
    <title>Liste des bureaux d'équipe</title>
    <meta charset="UTF-8">
    <style>
      @page {
        size: A4;
        margin: 15mm;
        @top-left { content: ""; }
        @top-center { content: ""; }
        @top-right { content: ""; }
        @bottom-left { content: ""; }
        @bottom-center { content: ""; }
        @bottom-right { content: ""; }
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
        
        .content-wrapper {
          margin-bottom: 80px;
        }
      }
      
      body { 
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        font-size: 12px;
        line-height: 1.4;
        color: #000;
        background: white;
      }
      
      .header {
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #ddd;
      }
      
      .company-logo {
        width: 80px;
        height: 80px;
        margin-right: 20px;
        border-radius: 8px;
        object-fit: contain;
        border: none !important;
      }
      
      .header-content {
        flex: 1;
      }
      
      .title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
      }
      
      .date {
        font-size: 11px;
        color: #666;
      }
      
      table { 
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 10px;
        background: white;
      }
      
      th, td { 
        border: 1px solid #ddd;
        padding: 6px 4px;
        text-align: left;
        vertical-align: top;
      }
      
      th { 
        background-color: #f8f9fa;
        font-weight: bold;
        font-size: 9px;
      }
      
      .signature-footer {
        position: fixed;
        bottom: 20px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #000;
        line-height: 1.2;
      }

      .company-info {
        margin-top: 10px;
        font-size: 8px;
        line-height: 1.3;
        color: #000;
      }
      
      .no-print {
        margin-top: 30px;
        text-align: center;
      }
      
      .print-btn, .close-btn {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 12px 24px;
        margin: 0 10px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
      }
      
      .close-btn {
        background-color: #6c757d;
      }
      
      .print-btn:hover {
        background-color: #0056b3;
      }
      
      .close-btn:hover {
        background-color: #545b62;
      }
      
      .badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: bold;
        color: #333;
        background-color: #f8f9fa;
        border: none;
      }
      
      .text-center {
        text-align: center;
      }
      
      @media print {
        .header {
          border-bottom: 1px solid #333;
        }
        
        th {
          background-color: #f0f0f0 !important;
        }
        
        .badge {
          background-color: transparent !important;
          border: none !important;
        }
      }
    </style>
  </head>
  <body>
    <div class="content-wrapper">
      <div class="header">
        ${this.companyInfo?.logo ? 
          `<img src="${this.getImageUrl(this.companyInfo.logo)}" alt="Logo ${this.companyInfo.nom}" class="company-logo" onerror="this.style.display='none'" />` : 
          '<div class="company-logo" style="display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666; background: #f8f9fa;">LOGO</div>'
        }
        <div class="header-content">
          <div class="title">Liste des Bureaux d'Équipe</div>
          <div class="date">Généré le ${formattedDate} à ${formattedTime}</div>
        </div>
      </div>
      
      <table>
        <thead>
          <tr>
            <th style="width: 25%;">Client</th>
            <th style="width: 15%;">Créé par</th>
            <th style="width: 20%;">Période</th>
            <th style="width: 12%;">Montant</th>
            <th style="width: 12%;">Situation</th>
            <th style="width: 10%;">Paiement</th>
            <th style="width: 6%;">Référence</th>
          </tr>
        </thead>
        <tbody>
          ${this.allFilteredData
            .map(
              (bureau) => `
            <tr>
              <td>${bureau.client_nom || 'N/A'}</td>
              <td>${bureau.created_by_nom || 'N/A'}</td>
              <td>${this.formatDate(bureau.date_debut)} - ${this.formatDate(bureau.date_fin)}</td>
              <td>${this.formatMontant(bureau.montant)}</td>
              <td>${this.formatSituation(bureau.situation)}</td>
              <td class="text-center">
                <span class="badge">
                  ${bureau.payement ? 'Payé' : 'Impayé'}
                </span>
              </td>
              <td>${bureau.reference_numero || '-'}</td>
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>
    </div>
    
    <div class="signature-footer">
      <div class="company-info">
        <div>
          ${this.companyInfo?.adresse || 'N/A'} | 
          Tél: ${this.companyInfo?.telephone || 'N/A'} | 
          Email: ${this.companyInfo?.email || 'N/A'}
        </div>
        <div>
          RC: ${this.companyInfo?.rc || 'N/A'} | 
          IF: ${this.companyInfo?.identifiant_fiscal || 'N/A'} | 
          TP: ${this.companyInfo?.tp || 'N/A'} | 
          ICE: ${this.companyInfo?.ice || 'N/A'}
        </div>
      </div>
    </div>
    
    <div class="no-print">
      <button onclick="window.print(); setTimeout(() => { window.close(); }, 1000);" class="print-btn">
        📄 Imprimer PDF
      </button>
      <button onclick="window.close()" class="close-btn">
        ✖ Fermer
      </button>
    </div>
    
    <script>
      window.onbeforeprint = function() {
        document.title = "";
      }
      
      window.onafterprint = function() {
        document.title = "Liste des bureaux d'équipe";
      }
      
      window.onload = function() {
        document.title = "Liste des bureaux d'équipe";
      }
    <\/script>
  </body>
</html>`;
    },

    // Méthode de secours si les infos de société ne sont pas disponibles
    generatePDFWithoutLogo() {
      const doc = new jsPDF();
      doc.autoTable = autoTable.bind(null, doc);
      
      doc.setFontSize(18);
      doc.text("Liste des bureaux d'équipe", 14, 20);
      doc.setFontSize(11);
      doc.text(`Exporté le: ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);
      
      doc.autoTable({
        head: [["Client", "Créé par", "Période", "Montant", "Situation", "Paiement", "Référence"]],
        body: this.allFilteredData.map((bureau) => [
          bureau.client_nom,
          bureau.created_by_nom,
          `${this.formatDate(bureau.date_debut)} - ${this.formatDate(bureau.date_fin)}`,
          this.formatMontant(bureau.montant),
          this.formatSituation(bureau.situation),
          bureau.payement ? 'Payé' : 'Impayé',
          bureau.reference_numero || '-'
        ]),
        startY: 40,
        styles: { fontSize: 9, font: "helvetica" },
        headStyles: {
          fillColor: [41, 128, 185],
          textColor: 255,
          fontSize: 10,
          fontStyle: "bold",
        },
        alternateRowStyles: { fillColor: [245, 245, 245] },
        margin: { top: 40, bottom: 30 },
      });
      
      if (this.companyInfo) {
        const finalY = doc.lastAutoTable.finalY || 40;
        doc.setFontSize(8);
        doc.text(`${this.companyInfo.nom || 'Société'}`, 14, finalY + 20);
        doc.text(`Adresse: ${this.companyInfo.adresse || 'N/A'} | Tél: ${this.companyInfo.telephone || 'N/A'}`, 14, finalY + 25);
        doc.text(`RC: ${this.companyInfo.rc || 'N/A'} - IF: ${this.companyInfo.identifiant_fiscal || 'N/A'} - TP: ${this.companyInfo.tp || 'N/A'} - ICE: ${this.companyInfo.ice || 'N/A'}`, 14, finalY + 30);
      }
      
      doc.save(`bureaux_equipe_${new Date().toISOString().split("T")[0]}.pdf`);
    },

    filterBureaux() {
      this.currentPage = 1;
    },

    onEditBureau(record) {
      this.isEditing = true;

      const start = new Date(record.date_debut);
      const end = new Date(record.date_fin);
      const diffTime = Math.abs(end - start);
      const diffMonths = Math.round(diffTime / (1000 * 60 * 60 * 24 * 30.44));

      this.formData = {
        id: record.id,
        client_id: record.client_id,
        gestionnaire_id: record.gestionnaire_id,
        date_debut: moment(record.date_debut).format('YYYY-MM-DD'),
        date_fin: moment(record.date_fin).format('YYYY-MM-DD'),
        duree_mois: diffMonths.toString(),
        montant: record.montant,
        situation: record.situation,
        payement: record.payement,
        reference_numero: record.reference_numero,
        notes: record.notes
      };
    },

    async onDeleteBureau(record) {
      const result = await Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Cette action supprimera le bureau définitivement.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Annuler",
        confirmButtonClass: "mx-3 my-2 p-2 btn btn-danger",
        cancelButtonClass: "mx-3 my-2 p-2 btn btn-secondary",
        buttonsStyling: false,
        reverseButtons: true,
        customClass: {
          actions: 'swal-actions-spaced'
        }
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/bureaux-equipe/${record.id}`);
          this.fetchBureaux();
          Swal.fire({
            icon: "success",
            title: "Succès",
            text: "Bureau supprimé avec succès",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
          });
        } catch (error) {
          Swal.fire({
            icon: "error",
            title: "Erreur",
            text:
              error.response?.data?.message ||
              "Échec de la suppression du bureau.",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
          });
        }
      }
    },

    redirectToAddClient() {
      this.closeModal("add-bureau");
      this.$router.push("/admin/addclient");
    },

    openAddGestionnaireModal() {
      this.$refs.usersModal.showModal();
    },

    onUserAdded(newUser) {
      this.fetchGestionnaires();
      this.formData.gestionnaire_id = newUser.id;
    },

    closeModal(modalId) {
      const modalElement = document.getElementById(modalId);
      if (modalElement) {
        const modal = Modal.getInstance(modalElement);
        if (modal) {
          modal.hide();
        }
      }
    },

    async fetchBureaux() {
      try {
        const [bureauxRes, clientsRes, gestionnairesRes, usersRes] = await Promise.all([
          axios.get("/bureaux-equipe"),
          axios.get("/clients"),
          axios.get("/gestionnaires"),
          axios.get("/users")
        ]);

        this.clients = clientsRes.data;
        this.gestionnaires = gestionnairesRes.data;
        this.users = usersRes.data;

        this.allBureaux = bureauxRes.data.map(bureau => {
          const client = this.clients.find(c => c.id === bureau.client_id) || {};
          const gestionnaire = this.gestionnaires.find(g => g.id === bureau.gestionnaire_id) || {};
          const createdBy = this.users.find(u => u.id === bureau.created_by) || {};

          return {
            ...bureau,
            client_nom: client.nom_francais || 'Inconnu',
            gestionnaire_nom: `${gestionnaire.nom || ''} ${gestionnaire.prenom || ''}`.trim() || 'Inconnu',
            created_by_nom: createdBy.name || 'Inconnu',
            payement: Boolean(bureau.payement)
          };
        });
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text: "Impossible de charger les bureaux.",
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 2000,
        });
      }
    }
  },

  async mounted() {
    await this.fetchCompanyInfo();
    this.fetchBureaux();
    this.formData.reference_numero = this.generateReference();
  },
};
</script>

<style scoped>
.form-group {
  margin-bottom: 1.5rem;
}

.badge-demande {
  background-color: #ffc107 !important;
  color: #000 !important;
}

.badge-en-cours {
  background-color: #28a745 !important;
  color: #fff !important;
}

.badge-rejette {
  background-color: #dc3545 !important;
  color: #fff !important;
}

/* Badges sans bordure pour paiement */
.badge-paye {
  display: inline-block;
  padding: 0.4em 0.8em;
  border-radius: 0.25rem;
  font-weight: 500;
  background-color: #28a745;
  color: #fff;
  border: none;
}

.badge-impaye {
  display: inline-block;
  padding: 0.4em 0.8em;
  border-radius: 0.25rem;
  font-weight: 500;
  background-color: #dc3545;
  color: #fff;
  border: none;
}

.form-label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: block;
}

.form-control,
.form-select {
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.75rem;
  width: 100%;
  transition: all 0.2s;
}

.form-control:focus,
.form-select:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.input-group {
  display: flex;
}

.input-group .form-select {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
  flex: 1;
}

.input-group .btn {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  z-index: 1;
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.form-check-label {
  margin-left: 0.5rem;
}

.custom-pagination-wrapper {
  background-color: #f8f9fa;
  border-radius: 6px;
  padding: 12px 16px;
}

.pagination-btn {
  min-width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #007bff;
  border-color: #007bff;
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-select {
  width: 70px;
  height: 36px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  text-align: center;
}

.page-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.pagination-info {
  font-size: 0.875rem;
  font-weight: 500;
}

.page-selector {
  background-color: white;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
}

@media (max-width: 768px) {
  .custom-pagination-wrapper {
    flex-direction: column;
    gap: 12px;
    align-items: stretch !important;
  }

  .pagination-controls {
    justify-content: center;
  }

  .pagination-info {
    text-align: center;
  }
}

.badge {
  padding: 0.4em 0.8em;
  border-radius: 0.25rem;
  font-weight: 500;
}

.bg-info {
  background-color: #0dcaf0 !important;
  color: #000;
}

.bg-warning {
  background-color: #ffc107 !important;
  color: #000;
}

.bg-danger {
  background-color: #dc3545 !important;
  color: #fff;
}

.bg-success {
  background-color: #28a745 !important;
  color: #fff;
}

.bg-secondary {
  background-color: #6c757d !important;
  color: #fff;
}

.filter-set {
  margin-top: 15px;
  flex-wrap: wrap;
}

.filter-group {
  min-width: 180px;
}

@media (max-width: 576px) {
  .filter-group {
    width: 100%;
  }
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.edit-btn:hover {
  background-color: #007bff;
  color: white;
}

.delete-btn:hover {
  background-color: #dc3545;
  color: white;
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
  text-decoration: none;
}

.table-top-head li a:hover {
  background-color: #e9ecef;
  color: #495057;
}

.table-top-head li a img {
  width: 20px;
  height: 20px;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .add-item {
    width: 100%;
    justify-content: space-between;
    flex-wrap: wrap;
  }
  
  .page-btn {
    margin-top: 1rem;
  }
}

.table-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 20px;
  gap: 20px;
}

.search-set {
  flex: 1;
  max-width: 300px;
}

.filter-set {
  display: flex;
  align-items: flex-end;
  gap: 15px;
}

.filter-group {
  min-width: 150px;
}

.filter-group .form-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6c757d;
}

@media (max-width: 768px) {
  .table-top {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }
  
  .search-set {
    max-width: 100%;
  }
  
  .filter-set {
    justify-content: flex-start;
    flex-wrap: wrap;
  }
}
.search-and-filters {
  width: 100%;
  flex-wrap: wrap;
}

.search-set {
  flex: 1;
  min-width: 250px;
  max-width: 400px;
}

.filters-inline {
  flex-shrink: 0;
}

.filter-group-inline {
  min-width: 160px;
}

.filter-group-inline .form-select {
  height: 38px;
  font-size: 0.875rem;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.375rem 0.75rem;
}

.filter-group-inline .form-select:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

@media (max-width: 992px) {
  .search-and-filters {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }
  
  .search-set {
    max-width: 100%;
  }
  
  .filters-inline {
    justify-content: flex-start;
  }
}

@media (max-width: 576px) {
  .filters-inline {
    flex-direction: column;
    gap: 10px;
  }
  
  .filter-group-inline {
    width: 100%;
  }
}

.search-and-filters {
  width: 100%;
  flex-wrap: nowrap;
  align-items: center;
}

.search-set {
  flex: 1;
  min-width: 250px;
  max-width: 400px;
}

.filters-inline {
  flex-shrink: 0;
  margin-left: 15px;
}

.filter-group-inline {
  min-width: 120px;
}

.filter-group-inline .form-select {
  height: 38px;
  font-size: 0.875rem;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.375rem 0.75rem;
  background-color: #fff;
}

.filter-group-inline .form-select:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

@media (max-width: 992px) {
  .search-and-filters {
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .filters-inline {
    margin-left: 0;
  }
}

@media (max-width: 576px) {
  .filters-inline {
    flex-direction: column;
    gap: 8px;
    width: 100%;
  }
  
  .filter-group-inline {
    width: 100%;
  }
}
</style>
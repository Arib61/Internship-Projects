<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <!-- Enhanced Header Section - Même style que Domiciliation.vue -->
      <div class="page-header modern-header" style="background-color: #ABC270;">
        <div class="header-content">
          <div class="page-title-section">
            <div class="title-wrapper">
              <div class="icon-wrapper">
                <i class="fas fa-history text-primary"></i>
              </div>
              <div class="title-content">
                <h4 class="page-title">Historique des Renouvellements</h4>
                <p class="page-subtitle">Journal des modifications des contrats de Burreaux</p>
              </div>
            </div>
          </div>

          <div class="header-actions">
            <div class="export-actions">
              <button class="action-btn export-btn" @click="exportToPDF" data-bs-toggle="tooltip"
                title="Exporter en PDF">
                <i class="fas fa-file-pdf"></i>
              </button>
              <button class="action-btn export-btn" @click="exportToExcel" data-bs-toggle="tooltip"
                title="Exporter en Excel">
                <i class="fas fa-file-excel"></i>
              </button>
             
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Filter Card - Style copié de Domiciliation.vue -->
      <div class="card filter-card">
        <div class="card-header">
          <h6 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtres et Recherche
          </h6>
          <button class="btn btn-sm btn-outline-secondary" @click="clearFilters">
            <i class="fas fa-times me-1"></i>
            Effacer
          </button>
        </div>
        <div class="card-body">
          <div class="filter-grid">
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-search me-1"></i>
                Recherche générale
              </label>
              <div class="search-input-wrapper">
                <input type="text" class="form-control search-input" placeholder="Rechercher..." v-model="search"
                  @input="filterHistories" />
                <i class="fas fa-search search-icon"></i>
              </div>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-user-cog me-1"></i>
                Modifié par
              </label>
              <select class="form-select" v-model="filters.modified_by" @change="filterHistories">
                <option value="">Tous les utilisateurs</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-calendar-alt me-1"></i>
                Année
              </label>
              <select class="form-select" v-model="filters.year" @change="filterHistories">
                <option value="">Toutes</option>
                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-calendar-week me-1"></i>
                Trimestre
              </label>
              <select class="form-select" v-model="filters.quarter" @change="filterHistories">
                <option value="">Tous</option>
                <option value="1">T1 (Jan-Mar)</option>
                <option value="2">T2 (Avr-Juin)</option>
                <option value="3">T3 (Juil-Sept)</option>
                <option value="4">T4 (Oct-Déc)</option>
              </select>
            </div>
          </div>

          <!-- Deuxième ligne de filtres -->
          <div class="filter-grid mt-3">
            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-cogs me-1"></i>
                Type d'opération
              </label>
              <select class="form-select" v-model="filters.operation_type" @change="filterHistories">
                <option value="">Tous</option>
                <option value="creation">Création</option>
                <option value="modification">Modification</option>
                <option value="renewal">Renouvellement</option>
                <option value="deletion">Suppression</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-tasks me-1"></i>
                Statut du contrat
              </label>
              <select class="form-select" v-model="filters.contract_status" @change="filterHistories">
                <option value="">Tous</option>
                <option value="DEMANDE">Demande</option>
                <option value="EN_COURS">En cours</option>
                <option value="REJETTE">Rejetée</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-credit-card me-1"></i>
                Paiement
              </label>
              <select class="form-select" v-model="filters.payment_status" @change="filterHistories">
                <option value="">Tous</option>
                <option value="paid">Payé</option>
                <option value="unpaid">Non payé</option>
              </select>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-clock me-1"></i>
                Durée (mois)
              </label>
              <div class="duration-filter">
                <input type="number" class="form-control" placeholder="Min" v-model="filters.duration_min" 
                  @input="filterHistories" min="1" max="36" />
                <span class="duration-separator">à</span>
                <input type="number" class="form-control" placeholder="Max" v-model="filters.duration_max" 
                  @input="filterHistories" min="1" max="36" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Data Table Card -->
      <div class="card data-table-card">
        <div class="card-body">
          <!-- Stats Summary -->
         
          <!-- Table Section -->
          <div class="table-container">
            <div class="accordion" id="historyAccordion">
              <div v-for="(group, index) in groupedData" :key="group.reference" class="accordion-item mb-3">
                <h2 class="accordion-header" :id="'heading' + index">
                  <button class="accordion-button collapsed modern-accordion-btn" type="button" data-bs-toggle="collapse"
                    :data-bs-target="'#collapse' + index" :aria-expanded="index === 0 ? 'true' : 'false'"
                    :aria-controls="'collapse' + index">
                    <div class="accordion-content">
                      <div class="accordion-main-info">
                        <span class="reference-badge">{{ group.reference }}</span>
                        <span class="modification-count">{{ group.histories.length }} modification(s)</span>
                        <span class="last-modification">Dernière modif: {{ formatDateTime(group.lastModificationDate) }}</span>
                      </div>
                      <div class="accordion-meta-info">
                        <span class="user-info">
                          
                          {{ group.lastModifiedBy }}
                        </span>
                        <span class="duration-info">
                          <i class="fas fa-clock me-1"></i>
                          {{ group.duration }} mois
                        </span>
                        <span :class="`status-badge ${getSituationClass(group.status)}`">
                          {{ getStatusText(group.status) }}
                        </span>
                        <span :class="`payment-badge ${group.payment ? 'paid' : 'unpaid'}`">
                          {{ group.payment ? "Payé" : "Non payé" }}
                        </span>
                      </div>
                    </div>
                  </button>
                </h2>
                <div :id="'collapse' + index" class="accordion-collapse collapse" :aria-labelledby="'heading' + index"
                  data-bs-parent="#historyAccordion">
                  <div class="accordion-body p-0">
                    <table class="table table-hover table-striped mb-0 modern-table">
                      <thead class="table-header">
                        <tr>
                          <th>Date</th>
                          <th>Modifié par</th>
                          <th>Type</th>
                          <th>Anciennes valeurs</th>
                          <th>Nouvelles valeurs</th>
                          
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="history in group.histories" :key="history.id" class="table-row">
                          <td class="date-cell">
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ formatDateTime(history.modification_date) }}
                          </td>
                          <td class="user-cell">
                            
                            {{ history.modified_by.name }}
                          </td>
                          <td>
                            <span :class="`operation-badge ${getOperationTypeClass(history)}`">
                              {{ getOperationType(history) }}
                            </span>
                          </td>
                          <td>
                            <div class="changes-container">
                              <div v-for="(value, key) in history.old_values" :key="key" class="change-item">
                                <span class="change-key">{{ formatKey(key) }}:</span>
                                <span class="change-old">{{ formatValue(key, value) }}</span>
                              </div>
                            </div>
                          </td>
                          <td>
                            <div class="changes-container">
                              <div v-for="(value, key) in history.new_values" :key="key" class="change-item">
                                <span class="change-key">{{ formatKey(key) }}:</span>
                                <span class="change-new">{{ formatValue(key, value) }}</span>
                              </div>
                            </div>
                          </td>
                          <td>
                            <button class="action-btn delete-action" @click="onDeleteHistory(history)" title="Supprimer">
                              <i class="fas fa-trash-alt"></i>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Enhanced Pagination -->
          <div class="pagination-wrapper">
            <div class="pagination-info">
              <div class="entries-selector">
                <label class="entries-label">Afficher :</label>
                <select v-model="pageSize" class="form-select entries-select" @change="onPageSizeChange">
                  <option value="5">5</option>
                  <option value="10">10</option>
                  <option value="25">25</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
                <span class="entries-text">références par page</span>
              </div>

              <div class="records-info">
                <span class="records-text">
                  Affichage de <strong>{{ startRecord }}</strong> à <strong>{{ endRecord }}</strong>
                  sur <strong>{{ allGroupedData.length }}</strong> références
                </span>
              </div>
            </div>

            <div class="pagination-controls">
              <button class="pagination-btn first-btn" :disabled="currentPage === 1" @click="goToPage(1)">
                <i class="fas fa-angle-double-left"></i>
              </button>
              <button class="pagination-btn prev-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
                <i class="fas fa-angle-left"></i>
              </button>

              <div class="page-selector">
                <span class="page-text">Page :</span>
                <select v-model="currentPage" class="form-select page-select" @change="onPageChange">
                  <option v-for="page in totalPages" :key="page" :value="page">{{ page }}</option>
                </select>
                <span class="page-total">sur {{ totalPages }}</span>
              </div>

              <button class="pagination-btn next-btn" :disabled="currentPage === totalPages"
                @click="goToPage(currentPage + 1)">
                <i class="fas fa-angle-right"></i>
              </button>
              <button class="pagination-btn last-btn" :disabled="currentPage === totalPages"
                @click="goToPage(totalPages)">
                <i class="fas fa-angle-double-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import Swal from "sweetalert2";
import { Collapse } from 'bootstrap';

export default {
  name: 'BurreauEquipeRenewHistory',
  data() {
    return {
      histories: [],
      users: [],
      search: "",
      filters: {
        reference: "",
        modified_by: "",
        modification_date: "",
        year: "",
        quarter: "",
        operation_type: "",
        contract_status: "",
        payment_status: "",
        duration_min: null,
        duration_max: null
      },
      pageSize: 10,
      currentPage: 1,
      isSubmitting: false,
      // Pour le regroupement
      groupedData: [],
      allGroupedData: [],
      availableYears: [],
      stats: {
        total: 0,
        renewals: 0,
        modifications: 0,
        creations: 0,
        deletions: 0
      }
    };
  },

  computed: {
    filteredHistories() {
      let filtered = [...this.histories];

      // Recherche générale
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (h) =>
            (h.domiciliation?.reference_numero && h.domiciliation.reference_numero.toLowerCase().includes(s)) ||
            (h.modified_by?.name || "").toLowerCase().includes(s) ||
            (h.notes && h.notes.toLowerCase().includes(s))
        );
      }

      // Filtres spécifiques
      if (this.filters.reference) {
        filtered = filtered.filter(h =>
          h.domiciliation?.reference_numero &&
          h.domiciliation.reference_numero.toLowerCase().includes(this.filters.reference.toLowerCase())
        );
      }

      if (this.filters.modified_by) {
        filtered = filtered.filter(h => h.modified_by && h.modified_by.id == this.filters.modified_by);
      }

      if (this.filters.modification_date) {
        filtered = filtered.filter(h => {
          const modDate = new Date(h.modification_date).toISOString().split('T')[0];
          return modDate === this.filters.modification_date;
        });
      }

      // Filtre par année
      if (this.filters.year) {
        filtered = filtered.filter(h => {
          const year = new Date(h.modification_date).getFullYear();
          return year == this.filters.year;
        });
      }

      // Filtre par trimestre
      if (this.filters.quarter) {
        filtered = filtered.filter(h => {
          const month = new Date(h.modification_date).getMonth() + 1;
          const quarter = Math.ceil(month / 3);
          return quarter == this.filters.quarter;
        });
      }

      // Filtre par type d'opération
      if (this.filters.operation_type) {
        filtered = filtered.filter(h => {
          return this.getOperationType(h) === this.filters.operation_type;
        });
      }

      // Filtre par statut de contrat
      if (this.filters.contract_status) {
        filtered = filtered.filter(h => {
          // Vérifier dans les nouvelles valeurs d'abord, puis dans les anciennes
          const status = h.new_values?.situation || h.old_values?.situation;
          return status === this.filters.contract_status;
        });
      }

      // Filtre par statut de paiement
      if (this.filters.payment_status) {
        filtered = filtered.filter(h => {
          // Vérifier dans les nouvelles valeurs d'abord, puis dans les anciennes
          const payment = h.new_values?.payement || h.old_values?.payement;
          if (this.filters.payment_status === 'paid') return payment === true;
          if (this.filters.payment_status === 'unpaid') return payment === false;
          return true;
        });
      }

      // Filtre par durée
      if (this.filters.duration_min || this.filters.duration_max) {
        filtered = filtered.filter(h => {
          // Calculer la durée en mois si les dates sont présentes
          let duration = 0;
          if (h.old_values?.date_debut && h.old_values?.date_fin) {
            duration = this.calculateMonthsDifference(h.old_values.date_debut, h.old_values.date_fin);
          } else if (h.new_values?.date_debut && h.new_values?.date_fin) {
            duration = this.calculateMonthsDifference(h.new_values.date_debut, h.new_values.date_fin);
          }
          
          if (this.filters.duration_min && duration < this.filters.duration_min) {
            return false;
          }
          if (this.filters.duration_max && duration > this.filters.duration_max) {
            return false;
          }
          return true;
        });
      }

      return filtered;
    },

    // Grouper les historiques par référence de domiciliation
    groupedHistories() {
      const groups = {};
      
      this.filteredHistories.forEach(history => {
        const reference = history.domiciliation?.reference_numero || 'Inconnue';
        
        if (!groups[reference]) {
          groups[reference] = {
            reference: reference,
            histories: [],
            lastModificationDate: '',
            lastModifiedBy: '',
            duration: 0,
            status: '',
            payment: false
          };
        }
        
        groups[reference].histories.push(history);
        
        // Trier les historiques par date décroissante
        groups[reference].histories.sort((a, b) => 
          new Date(b.modification_date) - new Date(a.modification_date)
        );
        
        // Mettre à jour la dernière date de modification
        if (!groups[reference].lastModificationDate || 
            new Date(history.modification_date) > new Date(groups[reference].lastModificationDate)) {
          groups[reference].lastModificationDate = history.modification_date;
          groups[reference].lastModifiedBy = history.modified_by?.name || 'Inconnu';
          
          // Mettre à jour la durée, le statut et le paiement à partir des dernières valeurs
          if (history.new_values) {
            if (history.new_values.date_debut && history.new_values.date_fin) {
              groups[reference].duration = this.calculateMonthsDifference(
                history.new_values.date_debut, 
                history.new_values.date_fin
              );
            }
            groups[reference].status = history.new_values.situation || '';
            groups[reference].payment = history.new_values.payement || false;
          }
        }
      });
      
      // Convertir en tableau et trier par dernière date de modification
      return Object.values(groups).sort((a, b) => 
        new Date(b.lastModificationDate) - new Date(a.lastModificationDate)
      );
    },

    totalPages() {
      return Math.ceil(this.allGroupedData.length / this.pageSize);
    },

    startRecord() {
      if (this.allGroupedData.length === 0) return 0;
      return (this.currentPage - 1) * this.pageSize + 1;
    },

    endRecord() {
      const end = this.currentPage * this.pageSize;
      return Math.min(end, this.allGroupedData.length);
    },

    // Données groupées paginées
    groupedData() {
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return this.allGroupedData.slice(start, end);
    }
  },

  watch: {
    groupedHistories: {
      immediate: true,
      handler(newVal) {
        this.allGroupedData = newVal;
        this.updateStats();
      }
    }
  },

  methods: {
    // ==================== API Configuration ====================
    getApiHeaders() {
      const token = localStorage.getItem("access_token");
      return {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
        Accept: "application/json",
      };
    },

    // ==================== Formatage des données ====================
    formatDateTime(dateString) {
      if (!dateString) return "-";
      const date = new Date(dateString);
      return date.toLocaleString("fr-FR");
    },

    formatKey(key) {
      const keyMap = {
        'client_id': 'Client',
        'gestionnaire_id': 'Gestionnaire',
        'date_debut': 'Date début',
        'date_fin': 'Date fin',
        'duree_mois': 'Durée (mois)',
        'montant': 'Montant',
        'situation': 'Situation',
        'payement': 'Paiement',
        'reference_numero': 'Référence'
        
      };
      return keyMap[key] || key;
    },

    formatValue(key, value) {
      if (key === 'date_debut' || key === 'date_fin') {
        return this.formatDate(value);
      }
      if (key === 'payement') {
        return value ? "Payé" : "Non payé";
      }
      if (key === 'situation') {
        return this.getSituationText(value);
      }
      if (key === 'montant') {
        return this.formatMontant(value);
      }
      return value;
    },

    formatDate(dateString) {
      if (!dateString) return "-";
      const date = new Date(dateString);
      return date.toLocaleDateString("fr-FR");
    },

    formatMontant(montant) {
      return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "MAD",
      }).format(montant);
    },

    getSituationText(situation) {
      switch (situation) {
        case "DEMANDE": return "Demande";
        case "EN_COURS": return "En cours";
        case "REJETTE": return "Rejetée";
        default: return situation;
      }
    },
    
    getStatusText(status) {
      return this.getSituationText(status);
    },
    
    getStatusClass(status) {
      return this.getSituationClass(status);
    },
    
    getSituationClass(situation) {
      switch (situation) {
        case "DEMANDE": return "bg-warning";
        case "EN_COURS": return "bg-success";
        case "REJETTE": return "bg-danger";
        default: return "bg-secondary";
      }
    },
    
    getOperationType(history) {
      if (!history.old_values && history.new_values) return "Création";
      if (!history.new_values && history.old_values) return "Suppression";
      if (history.notes?.toLowerCase().includes("renouvellement")) return "Renouvellement";
      return "Modification";
    },
    
    getOperationTypeClass(history) {
      const type = this.getOperationType(history);
      switch (type) {
        case "Création": return "bg-success";
        case "Modification": return "bg-info";
        case "Renouvellement": return "bg-primary";
        case "Suppression": return "bg-danger";
        default: return "bg-secondary";
      }
    },

    // ==================== CRUD Operations ====================
    async onDeleteHistory(history) {
      const result = await Swal.fire({
        title: "Êtes-vous sûr(e) ?",
        text: "Cette entrée d'historique sera définitivement supprimée !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui, supprimer !",
        cancelButtonText: "Annuler",
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/bureaux-equipe-histories/${history.id}`, {
            headers: this.getApiHeaders(),
          });

          this.showSuccessMessage("Entrée d'historique supprimée avec succès");
          await this.fetchHistories();
        } catch (error) {
          console.error("Erreur lors de la suppression de l'historique:", error);
          this.handleApiError(error);
        }
      }
    },

    // ==================== Filter & Pagination ====================
    clearFilters() {
      this.search = "";
      this.filters = {
        reference: "",
        modified_by: "",
        modification_date: "",
        year: "",
        quarter: "",
        operation_type: "",
        contract_status: "",
        payment_status: "",
        duration_min: null,
        duration_max: null
      };
      this.currentPage = 1;
    },

    filterHistories() {
      this.currentPage = 1;
    },

    onPageSizeChange() {
      this.currentPage = 1;
    },

    onPageChange() {
      // Page automatically updated via v-model
    },

    goToPage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
      }
    },

    // ==================== Export Methods ====================
    exportToExcel() {
      const dataToExport = this.filteredHistories.map((h) => ({
        Référence: h.domiciliation?.reference_numero || "-",
        "Modifié par": h.modified_by?.name || "-",
        "Date de modification": this.formatDateTime(h.modification_date),
        "Type d'opération": this.getOperationType(h),
        "Anciennes valeurs": this.formatChanges(h.old_values),
        "Nouvelles valeurs": this.formatChanges(h.new_values),
        Notes: h.notes || "-",
      }));

      const worksheet = XLSX.utils.json_to_sheet(dataToExport);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "Historique Bureaux");

      const excelBuffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });
      const data = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      saveAs(data, `historique_domiciliations_${new Date().toISOString().split("T")[0]}.xlsx`);
    },

    formatChanges(changes) {
      if (!changes) return "";
      return Object.entries(changes)
        .map(([key, value]) => `${this.formatKey(key)}: ${this.formatValue(key, value)}`)
        .join("\n");
    },

  exportToPDF() {
  if (!this.companyInfo) {
    this.generatePDFWithoutLogo();
    return;
  }

  // Créer une nouvelle fenêtre
  const printWindow = window.open('', '_blank');

  // Générer le contenu HTML
  const pdfContent = this.generatePrintableHTML();

  // Écrire le contenu dans la nouvelle fenêtre
  printWindow.document.open();
  printWindow.document.write(pdfContent);
  printWindow.document.close();

  // Configurer le titre et le focus
  printWindow.document.title = "Historique des bureaux équipe";
  printWindow.focus();
},

  // Dans la section methods
generatePrintableHTML() {
  const currentDate = new Date();
  const formattedDate = currentDate.toLocaleDateString('fr-FR');
  const formattedTime = currentDate.toLocaleTimeString('fr-FR');

  return `<!DOCTYPE html>
<html>
  <head>
    <title>Historique des bureaux équipe</title>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
      @page {
        size: A4;
        margin: 0;
      }
    
      @media print {
        @page {
          size: A4;
          margin: 0;
        }
        body {
          margin: 0;
          padding: 15px;
        }
        html, body {
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }
        
        /* Masquer tous les en-têtes et pieds de page du navigateur */
        @page :left, @page :right, @page :first {
          margin: 0;
          size: A4;
          marks: none;
        }
        
        .no-print, .no-print * {
          display: none !important;
        }
        
        .content-wrapper {
          margin-bottom: 80px;
        }
      }
      
      body { 
        font-family: 'Roboto', Arial, sans-serif;
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
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      .date {
        font-size: 11px;
        color: #666;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      table { 
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 10px;
        background: white;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      th, td { 
        border: 1px solid #ddd;
        padding: 6px 4px;
        text-align: left;
        vertical-align: top;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      th { 
        background-color: #f8f9fa;
        font-weight: bold;
        font-size: 9px;
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      .signature-footer {
        bottom: 20px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #000;
        line-height: 1.2;
        font-family: 'Roboto', Arial, sans-serif;
      }

      .company-info {
        margin-top: 10px;
        font-size: 8px;
        line-height: 1.3;
        color: #000;
        font-family: 'Roboto', Arial, sans-serif;
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
        font-family: 'Roboto', Arial, sans-serif;
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
        font-family: 'Roboto', Arial, sans-serif;
      }
      
      .text-center {
        text-align: center;
      }

      /* Styles spécifiques à l'historique */
      .group-header {
        background-color: #f8f9fa;
        padding: 8px;
        margin: 15px 0 5px 0;
        font-weight: bold;
        border-radius: 4px;
      }
      
      .change-item {
        margin-bottom: 3px;
      }
      
      .change-key {
        font-weight: bold;
        margin-right: 5px;
      }
      
      .change-old {
        color: #dc3545;
        text-decoration: line-through;
      }
      
      .change-new {
        color: #28a745;
      }
    </style>
  </head>
  <body>
    <div class="content-wrapper">
      <div class="header">
        ${this.companyInfo?.logo ?
          `<img src="${this.getImageUrl(this.companyInfo.logo)}" alt="Logo ${this.companyInfo.nom}" class="company-logo" onerror="this.style.display='none'" />` :
          '<div class="company-logo" style="display: flex; align-items: center; justify-content: center; font-size: 10px; color: #666; background: #f8f9fa; font-family: \'Roboto\', Arial, sans-serif;">LOGO</div>'
        }
        <div class="header-content">
          <div class="title">Historique des Bureaux Équipe</div>
          <div class="date">Généré le ${formattedDate} à ${formattedTime}</div>
        </div>
      </div>
      
      <!-- Stats Summary -->
      <div class="stats-summary" style="margin-bottom: 20px;">
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
          <div style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;">${this.stats.total}</div>
            <div style="font-size: 14px; color: #666;">Total</div>
          </div>
          <div style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;">${this.stats.renewals}</div>
            <div style="font-size: 14px; color: #666;">Renouvellements</div>
          </div>
          <div style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;">${this.stats.modifications}</div>
            <div style="font-size: 14px; color: #666;">Modifications</div>
          </div>
          <div style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;">${this.stats.creations}</div>
            <div style="font-size: 14px; color: #666;">Créations</div>
          </div>
          <div style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;">${this.stats.deletions}</div>
            <div style="font-size: 14px; color: #666;">Suppressions</div>
          </div>
        </div>
        <div style="font-size: 12px; color: #666; margin-bottom: 10px;">
          <strong>Filtres appliqués:</strong> ${this.getActiveFiltersText() || 'Aucun filtre actif'}
        </div>
      </div>
      
      <!-- Contenu de l'historique -->
      ${this.allGroupedData.map(group => `
        <div class="group-header">
          Référence: ${group.reference} - 
          Dernière modification: ${this.formatDateTime(group.lastModificationDate)} - 
          Modifié par: ${group.lastModifiedBy} - 
          Durée: ${group.duration} mois - 
          Statut: ${this.getStatusText(group.status)} - 
          Paiement: ${group.payment ? "Payé" : "Non payé"}
        </div>
        
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Modifié par</th>
              <th>Type</th>
              <th>Anciennes valeurs</th>
              <th>Nouvelles valeurs</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            ${group.histories.map(history => `
              <tr>
                <td>${this.formatDateTime(history.modification_date)}</td>
                <td>${history.modified_by?.name || '-'}</td>
                <td>
                  <span class="badge" style="background-color: ${this.getOperationTypeClass(history) === 'bg-success' ? '#28a745' :
            this.getOperationTypeClass(history) === 'bg-info' ? '#17a2b8' :
              this.getOperationTypeClass(history) === 'bg-primary' ? '#007bff' :
                this.getOperationTypeClass(history) === 'bg-danger' ? '#dc3545' : '#6c757d'};">
                    ${this.getOperationType(history)}
                  </span>
                </td>
                <td>
                  ${Object.entries(history.old_values || {}).map(([key, value]) => `
                    <div class="change-item">
                      <span class="change-key">${this.formatKey(key)}:</span>
                      <span class="change-old">${this.formatValue(key, value)}</span>
                    </div>
                  `).join('')}
                </td>
                <td>
                  ${Object.entries(history.new_values || {}).map(([key, value]) => `
                    <div class="change-item">
                      <span class="change-key">${this.formatKey(key)}:</span>
                      <span class="change-new">${this.formatValue(key, value)}</span>
                    </div>
                  `).join('')}
                </td>
                <td>${history.notes || '-'}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `).join('')}
    </div>
    
    <div class="no-print">
      <button onclick="window.print(); setTimeout(() => { window.close(); }, 1000);" class="print-btn">
        📄 Imprimer PDF
      </button>
      <button onclick="window.close()" class="close-btn">
        ✖ Fermer
      </button>
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
    
    <script>
      window.onload = function() {
        document.title = "Historique des bureaux équipe";
      }
    <\/script>
  </body>
</html>`;
},

async fetchCompanyInfo() {
  try {
    const response = await axios.get('/societes');

    if (response.data) {
      this.companyInfo = {
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
getImageUrl(path) {
  if (!path) return null;
  return `http://localhost:8000/storage/${path}`;
},
    
generatePDFWithoutLogo() {
  const doc = new jsPDF();
  doc.autoTable = autoTable.bind(null, doc);
  doc.setFontSize(18);
  doc.text("Historique des bureaux équipe", 14, 20);
  doc.setFontSize(11);
  doc.text(`Exporté le: ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);

  // Préparer les données pour le PDF
  const pdfData = this.allGroupedData.flatMap(group =>
    group.histories.map(history => [
      group.reference || "-",
      history.modified_by?.name || "-",
      this.formatDateTime(history.modification_date),
      this.getOperationType(history),
      this.formatChanges(history.old_values),
      this.formatChanges(history.new_values),
      history.notes || "-"
    ])
  );

  doc.autoTable({
    head: [["Référence", "Modifié par", "Date modification", "Type", "Anciennes valeurs", "Nouvelles valeurs", "Notes"]],
    body: pdfData,
    startY: 40,
    styles: { fontSize: 8, font: "helvetica" },
    headStyles: {
      fillColor: [171, 194, 112], // #ABC270
      textColor: 255,
      fontSize: 9,
      fontStyle: "bold",
    },
    alternateRowStyles: { fillColor: [245, 245, 245] },
    margin: { top: 40 },
  });

  doc.save(`historique_bureaux_equipe_${new Date().toISOString().split("T")[0]}.pdf`);
},
    getActiveFiltersText() {
      const activeFilters = [];
      
      if (this.filters.reference) activeFilters.push(`Référence: ${this.filters.reference}`);
      if (this.filters.modified_by) {
        const user = this.users.find(u => u.id == this.filters.modified_by);
        if (user) activeFilters.push(`Modifié par: ${user.name}`);
      }
      if (this.filters.year) activeFilters.push(`Année: ${this.filters.year}`);
      if (this.filters.quarter) {
        const quarters = {1: "T1 (Jan-Mar)", 2: "T2 (Avr-Juin)", 3: "T3 (Juil-Sept)", 4: "T4 (Oct-Déc)"};
        activeFilters.push(`Trimestre: ${quarters[this.filters.quarter]}`);
      }
      if (this.filters.operation_type) {
        const types = {
          creation: "Création", 
          modification: "Modification", 
          renewal: "Renouvellement", 
          deletion: "Suppression"
        };
        activeFilters.push(`Type: ${types[this.filters.operation_type]}`);
      }
      if (this.filters.contract_status) {
        activeFilters.push(`Statut: ${this.getSituationText(this.filters.contract_status)}`);
      }
      if (this.filters.payment_status) {
        activeFilters.push(`Paiement: ${this.filters.payment_status === 'paid' ? 'Payé' : 'Non payé'}`);
      }
      if (this.filters.duration_min || this.filters.duration_max) {
        const min = this.filters.duration_min || 0;
        const max = this.filters.duration_max || 36;
        activeFilters.push(`Durée: ${min}-${max} mois`);
      }
      
      return activeFilters.join(', ') || 'Aucun filtre actif';
    },

    // ==================== Data Fetching ====================
    async fetchHistories() {
      try {
        const response = await axios.get("/bureaux-equipe-histories", {
          headers: this.getApiHeaders(),
          params: {
            include: "domiciliation,modified_by"
          }
        });
        this.histories = response.data;
        this.extractAvailableYears();
      } catch (error) {
        console.error("Erreur lors du chargement de l'historique:", error);
        this.showErrorMessage("Impossible de charger l'historique");
      }
    },
    
    extractAvailableYears() {
      const years = new Set();
      this.histories.forEach(h => {
        if (h.modification_date) {
          const year = new Date(h.modification_date).getFullYear();
          years.add(year);
        }
      });
      this.availableYears = Array.from(years).sort((a, b) => b - a); // tri décroissant
    },

    async fetchUsers() {
      try {
        const response = await axios.get("/users", {
          headers: this.getApiHeaders(),
        });
        this.users = response.data.data || response.data;
      } catch (error) {
        console.error("Erreur lors du chargement des utilisateurs:", error);
      }
    },
    
    updateStats() {
      this.stats = {
        total: this.allGroupedData.length,
        renewals: this.allGroupedData.filter(g => 
          g.histories.some(h => this.getOperationType(h) === 'Renouvellement')
        ).length,
        modifications: this.allGroupedData.filter(g => 
          g.histories.some(h => this.getOperationType(h) === 'Modification')
        ).length,
        creations: this.allGroupedData.filter(g => 
          g.histories.some(h => this.getOperationType(h) === 'Création')
        ).length,
        deletions: this.allGroupedData.filter(g => 
          g.histories.some(h => this.getOperationType(h) === 'Suppression')
        ).length
      };
    },

    // ==================== Error Handling ====================
    handleApiError(error) {
      if (error.response) {
        const errorMessage = error.response.data.error || error.response.data.details || "Erreur lors de l'opération";

        if (error.response.status === 401) {
          this.showErrorMessage("Session expirée. Veuillez vous reconnecter.");
        } else if (error.response.status === 422) {
          const validationErrors = error.response.data.errors;
          const errorMessages = Object.values(validationErrors).flat();
          this.showErrorMessage(errorMessages.join("\n"));
        } else {
          this.showErrorMessage(errorMessage);
        }
      } else {
        this.showErrorMessage("Erreur de connexion au serveur");
      }
    },

    // ==================== Success/Error Messages ====================
    showSuccessMessage(message) {
      Swal.fire({
        icon: "success",
        title: "Succès!",
        text: message,
        timer: 3000,
        showConfirmButton: false,
        position: "top-end",
        toast: true,
      });
    },

    showErrorMessage(message) {
      Swal.fire({
        icon: "error",
        title: "Erreur!",
        text: message,
        confirmButtonText: "OK",
      });
    },
    
    calculateMonthsDifference(startDate, endDate) {
      const start = new Date(startDate);
      const end = new Date(endDate);
      return (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
    }
  },

async mounted() {
  await this.fetchCompanyInfo();
  await Promise.all([this.fetchHistories(), this.fetchUsers()]);
},
};
</script>

<style scoped>
/* ==================== Modern Header Styles (copié de Domiciliation.vue) ==================== */
.modern-header {
  background: #ABC270 !important;
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  color: white;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.page-title-section {
  display: flex;
  align-items: center;
  gap: 2rem;
  flex: 1;
}

.title-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.icon-wrapper {
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.title-content h4 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: white;
}

.page-subtitle {
  margin: 0.25rem 0 0 0;
  opacity: 0.9;
  font-size: 0.95rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.export-actions {
  display: flex;
  gap: 0.5rem;
  margin-right: 1rem;
}

.export-btn {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.export-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}

/* ==================== Filter Card Styles (copié de Domiciliation.vue) ==================== */
.filter-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
  overflow: hidden;
}

.filter-card .card-header {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-weight: 600;
  color: #495057;
  display: flex;
  align-items: center;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
}

.filter-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
}

.search-input-wrapper {
  position: relative;
}

.search-input {
  padding-right: 2.5rem;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.search-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.search-icon {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
}

.form-control,
.form-select {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  padding: 0.75rem;
  transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.duration-filter {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.duration-separator {
  color: #6c757d;
  font-weight: 500;
}

/* ==================== Data Table Card Styles ==================== */
.data-table-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.table-container {
  background: white;
}

/* ==================== Stats Summary Styles ==================== */
.stats-summary {
  margin: 20px 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.stat-card {
  padding: 1.5rem 1.25rem;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 0.9;
}

/* ==================== Accordion Styles ==================== */
.accordion-item {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.modern-accordion-btn {
  padding: 1.25rem 1.5rem;
  font-weight: 500;
  background-color: #f8f9fa;
  border: none;
  transition: all 0.3s ease;
}

.modern-accordion-btn:not(.collapsed) {
  background-color: #e9ecef;
  color: #212529;
  box-shadow: none;
}

.modern-accordion-btn:hover {
  background-color: #e9ecef;
}

.accordion-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.accordion-main-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}


.modification-count {
  background: #6c757d;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.last-modification {
  color: #6c757d;
  font-size: 0.875rem;
}

.accordion-meta-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info,
.duration-info {
  color: #495057;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.payment-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.payment-badge.paid {
  background-color: #198754;
}

.payment-badge.unpaid {
  background-color: #dc3545;
}

/* ==================== Table Styles ==================== */
.modern-table {
  margin: 0;
}

.table-header th {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 2px solid #dee2e6;
  font-weight: 600;
  color: #495057;
  padding: 1rem 0.75rem;
  font-size: 0.875rem;
}

.table-row {
  transition: all 0.3s ease;
}

.table-row:hover {
  background-color: #f8f9fa;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.date-cell,
.user-cell {
  color: #6c757d;
  font-size: 0.875rem;
}

.operation-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  color: white;
}

.changes-container {
  max-width: 300px;
}

.change-item {
  display: flex;
  margin-bottom: 3px;
  line-height: 1.3;
}

.change-key {
  font-weight: 600;
  margin-right: 5px;
  flex-shrink: 0;
}

.change-old {
  color: #dc3545;
  text-decoration: line-through;
}

.change-new {
  color: #28a745;
  font-weight: 500;
}

.notes-cell {
  max-width: 200px;
  word-wrap: break-word;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  cursor: pointer;
  font-size: 0.875rem;
}

.delete-action {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
}

.delete-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

/* ==================== Pagination Styles (copié de Domiciliation.vue) ==================== */
.pagination-wrapper {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  padding: 1.5rem;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.pagination-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.entries-selector {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.entries-label {
  font-weight: 600;
  color: #495057;
  font-size: 0.875rem;
}

.entries-select {
  width: auto;
  min-width: 80px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  padding: 0.375rem 0.75rem;
}

.entries-text {
  color: #6c757d;
  font-size: 0.875rem;
}

.records-info {
  display: flex;
  align-items: center;
}

.records-text {
  color: #495057;
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn {
  width: 40px;
  height: 40px;
  border: 1px solid #dee2e6;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  cursor: pointer;
}


.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-selector {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  margin: 0 1rem;
}

.page-text,
.page-total {
  color: #6c757d;
  font-size: 0.875rem;
}

.page-select {
  width: 70px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 0.25rem 0.5rem;
  text-align: center;
}

/* ==================== Responsive Design ==================== */
@media (max-width: 1200px) {
  .filter-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  }
}

@media (max-width: 768px) {
  .modern-header {
    padding: 1.5rem;
  }

  .header-content {
    flex-direction: column;
    align-items: stretch;
  }

  .page-title-section {
    flex-direction: column;
    gap: 1rem;
  }

  .title-wrapper {
    flex-direction: column;
    text-align: center;
  }

  .header-actions {
    flex-direction: column;
    gap: 1rem;
  }

  .export-actions {
    justify-content: center;
    margin-right: 0;
  }

  .filter-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .accordion-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .accordion-meta-info {
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .pagination-info {
    flex-direction: column;
    gap: 1rem;
  }

  .pagination-controls {
    flex-wrap: wrap;
  }

  .page-selector {
    margin: 0;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  }

  .stat-card {
    padding: 1rem 0.75rem;
  }

  .stat-value {
    font-size: 1.5rem;
  }
}

@media (max-width: 576px) {
  .modern-header {
    padding: 1rem;
  }

  .icon-wrapper {
    width: 50px;
    height: 50px;
    font-size: 1.25rem;
  }

  .title-content h4 {
    font-size: 1.5rem;
  }

  .export-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
  }

  .accordion-main-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .accordion-meta-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }

  .changes-container {
    max-width: 100%;
  }

  .duration-filter {
    flex-direction: column;
    gap: 0.25rem;
  }

  .duration-separator {
    display: none;
  }
}
</style>
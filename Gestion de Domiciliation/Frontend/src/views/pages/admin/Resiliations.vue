<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <!-- Enhanced Header Section - Même style que DomiciliationRenewHistory.vue -->
      <div class="page-header modern-header" style="background-color: #ABC270;">
        <div class="header-content">
          <div class="page-title-section">
            <div class="title-wrapper">
              <div class="icon-wrapper">
                <i class="fas fa-ban text-primary"></i>
              </div>
              <div class="title-content">
                <h4 class="page-title">Gestion des Résiliations</h4>
                <p class="page-subtitle">Gérez vos résiliations de contrats de domiciliation</p>
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

      <!-- Enhanced Filter Card - Style copié de DomiciliationRenewHistory.vue -->
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
                  @input="filterResiliations" />
                <i class="fas fa-search search-icon"></i>
              </div>
            </div>



            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-calendar-alt me-1"></i>
                Date de résiliation
              </label>
              <input type="date" class="form-control" v-model="filters.date" @change="filterResiliations" />
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Raison
              </label>
              <select class="form-select" v-model="filters.raison" @change="filterResiliations">
                <option value="">Toutes les raisons</option>
                <option value="FIN_CONTRAT">Fin de contrat</option>
                <option value="INSATISFACTION">Insatisfaction</option>
                <option value="DEMENAGEMENT">Déménagement</option>
                <option value="AUTRE">Autre</option>
              </select>
            </div>


          </div>

          <!-- Deuxième ligne de filtres -->

        </div>
      </div>

      <!-- Enhanced Data Table Card -->
      <div class="card data-table-card">
        <div class="card-body">
          <!-- Table Section -->
          <div class="table-container">
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0 modern-table">
                <thead class="table-header">
                  <tr>
                    <th>
                      <i class="fas fa-hashtag me-1"></i>
                      Référence
                    </th>
                    <th>
                      <i class="fas fa-calendar-alt me-1"></i>
                      Date
                    </th>
                    <th>
                      <i class="fas fa-exclamation-triangle me-1"></i>
                      Raison
                    </th>


                    <th>
                      <i class="fas fa-cogs me-1"></i>
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="record in filteredData" :key="record.id" class="table-row">
                    <td class="reference-cell">
                      <span class="reference-badge">{{ record.domiciliation?.reference_numero || "-" }}</span>
                    </td>
                    <td class="date-cell">
                      <i class="fas fa-calendar-alt me-1"></i>
                      {{ formatDate(record.date_resiliation) }}
                    </td>
                    <td>
                      <span :class="reference - badge">
                        {{ getRaisonText(record.raison) }}
                      </span>
                    </td>


                    <td>
                      <div class="action-buttons">
                        <button class="action-btn download-action" @click="downloadContract(record)"
                          :disabled="isDownloading && downloadingId === record.id"
                          title="Télécharger le contrat (PDF ou Word)">

                          <!-- Icône normale -->
                          <i class="fas fa-download" v-if="!(isDownloading && downloadingId === record.id)"></i>

                          <!-- Spinner de chargement -->
                          <div v-else class="spinner-border spinner-border-sm" role="status"></div>
                        </button>
                        <button class="action-btn delete-action" @click="onDeleteResiliation(record)" title="Supprimer">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
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
                <span class="entries-text">entrées par page</span>
              </div>

              <div class="records-info">
                <span class="records-text">
                  Affichage de <strong>{{ startRecord }}</strong> à <strong>{{ endRecord }}</strong>
                  sur <strong>{{ allFilteredData.length }}</strong> résiliations
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
import html2pdf from 'html2pdf.js';
import {
  Document,
  Paragraph,
  TextRun,
  AlignmentType,
  Packer,
  Table,
  TableRow,
  TableCell,
  WidthType,
  BorderStyle,
  Footer,
  Header,
  ImageRun,
} from "docx"

export default {
  name: "ResiliationsList",
  data() {
    return {
      resiliations: [],
      domiciliations: [],
      search: "",
      filters: {
        reference: "",
        date: "",
        raison: "",
        status: "",
      },
      pageSize: 10,
      currentPage: 1,
      isSubmitting: false,
      downloadingId: null,
    };
  },

  computed: {
    filteredData() {
      let filtered = [...this.resiliations];

      // General search
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (r) =>
            (r.domiciliation?.reference_numero && r.domiciliation.reference_numero.toLowerCase().includes(s)) ||
            (r.raison && r.raison.toLowerCase().includes(s)) ||
            (r.status && r.status.toLowerCase().includes(s)) ||
            (r.commentaires && r.commentaires.toLowerCase().includes(s))
        );
      }

      // Specific filters
      if (this.filters.reference) {
        filtered = filtered.filter(r =>
          r.domiciliation?.reference_numero && r.domiciliation.reference_numero.toLowerCase().includes(this.filters.reference.toLowerCase())
        );
      }

      if (this.filters.date) {
        const filterDate = new Date(this.filters.date).toISOString().split('T')[0];
        filtered = filtered.filter(r => {
          const resDate = new Date(r.date_resiliation).toISOString().split('T')[0];
          return resDate === filterDate;
        });
      }

      if (this.filters.raison) {
        filtered = filtered.filter(r => r.raison === this.filters.raison);
      }

      if (this.filters.status) {
        filtered = filtered.filter(r => r.status === this.filters.status);
      }

      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return filtered.slice(start, end);
    },

    allFilteredData() {
      let filtered = [...this.resiliations];

      // General search
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (r) =>
            (r.domiciliation?.reference_numero && r.domiciliation.reference_numero.toLowerCase().includes(s)) ||
            (r.raison && r.raison.toLowerCase().includes(s)) ||
            (r.status && r.status.toLowerCase().includes(s)) ||
            (r.commentaires && r.commentaires.toLowerCase().includes(s))
        );
      }

      // Specific filters
      if (this.filters.reference) {
        filtered = filtered.filter(r =>
          r.domiciliation?.reference_numero && r.domiciliation.reference_numero.toLowerCase().includes(this.filters.reference.toLowerCase())
        );
      }

      if (this.filters.date) {
        const filterDate = new Date(this.filters.date).toISOString().split('T')[0];
        filtered = filtered.filter(r => {
          const resDate = new Date(r.date_resiliation).toISOString().split('T')[0];
          return resDate === filterDate;
        });
      }

      if (this.filters.raison) {
        filtered = filtered.filter(r => r.raison === this.filters.raison);
      }

      if (this.filters.status) {
        filtered = filtered.filter(r => r.status === this.filters.status);
      }

      return filtered;
    },

    totalPages() {
      return Math.ceil(this.allFilteredData.length / this.pageSize) || 1;
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

   

    // ==================== API Configuration ====================
    getApiHeaders() {
      const token = localStorage.getItem("access_token");
      return {
        Authorization: `Bearer ${token}`,
        "Content-Type": "application/json",
        Accept: "application/json",
      };
    },

    // ==================== Utility Methods ====================
    formatDate(date) {
      if (!date) return "-";
      return new Date(date).toLocaleDateString("fr-FR");
    },



    getRaisonText(raison) {
      switch (raison) {
        case "FIN_CONTRAT":
          return "Fin de contrat";
        case "INSATISFACTION":
          return "Insatisfaction";
        case "DEMENAGEMENT":
          return "Déménagement";
        case "AUTRE":
          return "Autre";
        default:
          return raison;
      }
    },

    getStatusClass(status) {
      switch (status) {
        case "EN_ATTENTE":
          return "status-badge bg-warning";
        case "APPROUVEE":
          return "status-badge bg-success";
        case "REJETEE":
          return "status-badge bg-danger";
        case "ANNULEE":
          return "status-badge bg-dark";
        default:
          return "status-badge bg-secondary";
      }
    },

    getStatusText(status) {
      switch (status) {
        case "EN_ATTENTE":
          return "En attente";
        case "APPROUVEE":
          return "Approuvée";
        case "REJETEE":
          return "Rejetée";
        case "ANNULEE":
          return "Annulée";
        default:
          return status;
      }
    },

    // ==================== Filter & Pagination ====================
    clearFilters() {
      this.search = "";
      this.filters = {
        reference: "",
        date: "",
        raison: "",
        status: "",
      };
      this.currentPage = 1;
    },

    filterResiliations() {
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
      const dataToExport = this.allFilteredData.map((res) => ({
        Référence: res.domiciliation?.reference_numero || "-",
        Date: this.formatDate(res.date_resiliation),
        Raison: this.getRaisonText(res.raison),
        Status: this.getStatusText(res.status),

      }));

      const worksheet = XLSX.utils.json_to_sheet(dataToExport);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "Résiliations");

      const excelBuffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });
      const data = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      saveAs(data, `resiliations_${new Date().toISOString().split("T")[0]}.xlsx`);
    },

    // Dans les méthodes du composant ResiliationsList
    exportToPDF() {
      if (!this.companyInfo) {
        this.generatePDFWithoutLogo();
        return;
      }

      // Créer une nouvelle fenêtre sans URL blob
      const printWindow = window.open('', '_blank');

      // Générer le contenu HTML
      const pdfContent = this.generatePrintableHTML();

      // Écrire le contenu directement dans la nouvelle fenêtre
      printWindow.document.open();
      printWindow.document.write(pdfContent);
      printWindow.document.close();

      // Configurer le titre et le focus
      printWindow.document.title = "Liste des résiliations";
      printWindow.focus();
    },

    // Générer le HTML pour l'impression
    generatePrintableHTML() {
      const currentDate = new Date();
      const formattedDate = currentDate.toLocaleDateString('fr-FR');
      const formattedTime = currentDate.toLocaleTimeString('fr-FR');

      return `<!DOCTYPE html>
<html>
  <head>
    <title>Liste des résiliations</title>
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
        position: fixed;
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
          <div class="title">Liste des Résiliations</div>
          <div class="date">Généré le ${formattedDate} à ${formattedTime}</div>
        </div>
      </div>
      
      <table>
        <thead>
          <tr>
            <th style="width: 15%;">Référence</th>
            <th style="width: 20%;">Date</th>
            <th style="width: 20%;">Raison</th>
            <th style="width: 15%;">Statut</th>
            <th style="width: 30%;">Commentaires</th>
          </tr>
        </thead>
        <tbody>
          ${this.allFilteredData
          .map(
            (res) => `
            <tr>
              <td>${res.domiciliation?.reference_numero || '-'}</td>
              <td>${this.formatDate(res.date_resiliation)}</td>
              <td>${this.getRaisonText(res.raison)}</td>
              <td class="text-center">
                <span class="badge">
                  ${this.getStatusText(res.status)}
                </span>
              </td>
              <td>${res.commentaires || '-'}</td>
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
      window.onload = function() {
        document.title = "Liste des résiliations";
      }
    <\/script>
  </body>
</html>`;
    },

    // Méthode de secours si les infos de société ne sont pas disponibles
    generatePDFWithoutLogo() {
      const doc = new jsPDF();
      doc.autoTable = autoTable.bind(null, doc);

      try {
        doc.setFont("Roboto", "normal");
      } catch (e) {
        doc.setFont("helvetica", "normal");
      }

      doc.setFontSize(18);
      doc.text("Liste des résiliations", 14, 20);
      doc.setFontSize(11);
      doc.text(`Exporté le: ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);

      doc.autoTable({
        head: [["Référence", "Date", "Raison", "Statut", "Commentaires"]],
        body: this.allFilteredData.map((res) => [
          res.domiciliation?.reference_numero || '-',
          this.formatDate(res.date_resiliation),
          this.getRaisonText(res.raison),
          this.getStatusText(res.status),
          res.commentaires || '-'
        ]),
        startY: 40,
        styles: { fontSize: 9, font: "Roboto" },
        headStyles: {
          fillColor: [171, 194, 112], // #ABC270
          textColor: 255,
          fontSize: 10,
          fontStyle: "bold",
          font: "Roboto"
        },
        alternateRowStyles: { fillColor: [245, 245, 245] },
        margin: { top: 40, bottom: 30 },
      });

      if (this.companyInfo) {
        const finalY = doc.lastAutoTable.finalY || 40;
        doc.setFontSize(8);
        doc.text(`${this.companyInfo.nom || 'Société'}`, 14, finalY + 20);
        doc.text(`Adresse: ${this.companyInfo.adresse || 'N/A'} | Tél: ${this.companyInfo.telephone || 'N/A'}`, 14, finalY + 25);
        doc.text(`RC: ${this.companyInfo.rc || 'N/A'}  IF: ${this.companyInfo.identifiant_fiscal || 'N/A'}  TP: ${this.companyInfo.tp || 'N/A'}  ICE: ${this.companyInfo.ice || 'N/A'}`, 14, finalY + 30);
      }

      doc.save(`resiliations_${new Date().toISOString().split("T")[0]}.pdf`);
    },

    // Fetch company information
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

    // Get image URL for Laravel Storage
    getImageUrl(path) {
      if (!path) return null;
      return `http://localhost:8000/storage/${path}`;
    },

    async downloadContract(record) {
      this.downloadingId = record.id;
      this.isDownloading = true;

      try {
        const result = await Swal.fire({
          title: "Télécharger le contrat",
          text: "Quel format souhaitez-vous télécharger ?",
          icon: "question",
          showCancelButton: true,
          showDenyButton: true,
          confirmButtonText: "PDF",
          denyButtonText: "Word",
          cancelButtonText: "Plus tard",
          confirmButtonColor: "#dc3545",
          denyButtonColor: "#0d6efd",
        });

        if (result.isConfirmed) {
          // 📥 Télécharger PDF via redirection directe
          this.downloadContractPDF(record.id);
        } else if (result.isDenied) {
          // 📥 Télécharger Word via redirection directe
          this.downloadContractWORD(record.id);
        }

      } catch (error) {
        console.error("Erreur lors du téléchargement :", error);
        Swal.fire("Erreur", "Échec du téléchargement du contrat", "error");
      } finally {
        this.isDownloading = false;
        this.downloadingId = null;
      }
    },
    // ==================== PDF Download ====================
    async downloadPDF(record) {
      try {
        this.downloadingId = record.id;
        this.isSubmitting = true;

        const apiUrl = `${process.env.VUE_APP_API_URL || ''}/resiliations/${record.id}/pdf`;
        const token = localStorage.getItem("access_token");

        const response = await fetch(apiUrl, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        });

        if (!response.ok) {
          throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `resiliation_${record.id}.pdf`);

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

        this.showSuccessMessage("Document PDF téléchargé avec succès");
      } catch (error) {
        console.error("Erreur lors du téléchargement du PDF:", error);
        this.showErrorMessage("Erreur lors du téléchargement du PDF: " + (error.message || "Erreur inconnue"));
      } finally {
        this.isSubmitting = false;
        this.downloadingId = null;
      }
    },

    async downloadContractPDF(id) {
      try {
        const response = await axios.get(`/resiliations/resiliationContractPDF/${id}`, {
          headers: this.getApiHeaders(),
        });
        const { htmlContract } = response.data;

        const generatePdf = async (htmlContent, filename) => {
          const container = document.createElement("div");
          container.innerHTML = htmlContent;
          document.body.appendChild(container);

          await html2pdf()
            .from(document.getElementById("pdf-container"))
            .set({
              margin: [15, 5, 20, 5],
              html2canvas: {
                scale: 2,
                useCORS: true,
                letterRendering: true,
                dpi: 300,
              },
              image: { type: "png", quality: 1 },
              jsPDF: {
                unit: "mm",
                format: "letter",
                orientation: "portrait",
                letterRendering: true,
              },
              filename,
            })
            .save();


          container.remove(); // Clean up
        };

        await generatePdf(htmlContract, `resiliation_contrat_${id}.pdf`);
      } catch (error) {
        console.error("Error generating contract PDFs:", error);
      }
    },



    async downloadContractWORD(id) {
      try {
        const response = await axios.get(`/generate-contract-word/${id}`);
        const data1 = response.data.data1;

        const societe = data1.societe || {};
        const domiciliation = data1.domiciliation || {};
        const client = data1.client || {};
        const gerant = data1.gerant || {};

        // Helper: Convert image to base64 (SVG/PNG/JPG)
        const getImageAsBase64 = async (imageUrl, width = 150, height = 75) => {
          if (!imageUrl) return null;
          try {
            if (imageUrl.endsWith(".svg") || imageUrl.includes("image/svg+xml")) {
              const response = await fetch(imageUrl);
              if (!response.ok) return null;
              const svgText = await response.text();
              return await new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = "anonymous";
                const svgBlob = new Blob([svgText], { type: "image/svg+xml;charset=utf-8" });
                const url = URL.createObjectURL(svgBlob);
                img.onload = () => {
                  try {
                    const canvas = document.createElement("canvas");
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext("2d");
                    ctx.fillStyle = "white";
                    ctx.fillRect(0, 0, width, height);
                    // Center the SVG
                    const aspectRatio = img.naturalWidth / img.naturalHeight;
                    let drawWidth = width;
                    let drawHeight = height;
                    if (aspectRatio > width / height) {
                      drawHeight = width / aspectRatio;
                    } else {
                      drawWidth = height * aspectRatio;
                    }
                    const x = (width - drawWidth) / 2;
                    const y = (height - drawHeight) / 2;
                    ctx.drawImage(img, x, y, drawWidth, drawHeight);
                    const pngBase64 = canvas.toDataURL("image/png", 1.0).split(",")[1];
                    URL.revokeObjectURL(url);
                    resolve(pngBase64);
                  } catch (err) {
                    URL.revokeObjectURL(url);
                    reject(err);
                  }
                };
                img.onerror = () => {
                  URL.revokeObjectURL(url);
                  reject(new Error("Failed to load SVG image"));
                };
                img.src = url;
              });
            } else {
              const response = await fetch(imageUrl, {
                method: "GET",
                mode: "cors",
                credentials: "omit",
                headers: { Accept: "image/*" },
              });
              if (!response.ok) return null;
              const blob = await response.blob();
              if (blob.size === 0) return null;
              return await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onloadend = () => {
                  const result = reader.result;
                  if (!result || typeof result !== "string") {
                    reject(new Error("Failed to read image as data URL"));
                    return;
                  }
                  const base64String = result.split(",")[1];
                  if (!base64String) {
                    reject(new Error("Invalid data URL format"));
                    return;
                  }
                  resolve(base64String);
                };
                reader.onerror = () => reject(new Error("FileReader error"));
                reader.readAsDataURL(blob);
              });
            }
          } catch {
            return null;
          }
        };

        // Helper: Get proper image URL
        async function getImageUrl(imagePath) {
          if (!imagePath) return null;
          if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) return imagePath;
          const baseUrls = [
            "http://localhost:8000/api/storage/",
          ];
          for (const baseUrl of baseUrls) {
            const testUrl = baseUrl + imagePath.replace(/^\/+/, "");
            try {
              const response = await fetch(testUrl, {
                method: "HEAD",
                mode: "cors",
                credentials: "omit",
              });
              if (response.ok) return testUrl;
            } catch { }
          }
          return null;
        }

        // Format dates in Morocco timezone
        const formatDate = (dateStr) => {
          if (!dateStr) return "";
          return new Date(dateStr).toLocaleDateString("fr-FR", {
            timeZone: "Africa/Casablanca",
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
          });
        };
        const nowDate = new Date().toLocaleDateString("fr-FR", {
          timeZone: "Africa/Casablanca",
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        });
        const dateCreation = formatDate(domiciliation?.created_at) || "N/A"
        const dateFin = formatDate(domiciliation?.date_fin) || "N/A";

        // Get logo as base64 if available
        let logoBase64 = null;
        if (societe.logo) {
          const logoUrl = await getImageUrl(societe.logo);
          if (logoUrl) {
            logoBase64 = await getImageAsBase64(logoUrl);
          }
        }
        if (!logoBase64) {
          const fallbackUrl = "https://meconsulting.ma/wp-content/uploads/2023/12/Mansouri_Logo.svg";
          logoBase64 = await getImageAsBase64(fallbackUrl);
        }

        // Build the Word document with vertical and horizontal centering
        const doc = new Document({
          sections: [
            {
              properties: {
                page: {
                  margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
                },
              },
              children: [
                new Paragraph({
                  children: [
                    ...(logoBase64
                      ? [
                        new ImageRun({
                          data: Uint8Array.from(atob(logoBase64), c => c.charCodeAt(0)),
                          transformation: { width: 150, height: 75 },
                        }),
                      ]
                      : []),
                  ],
                  spacing: { after: logoBase64 ? 400 : 0 },
                  alignment: AlignmentType.RIGHT,
                }),
                // Centered content in the middle of the page
                new Paragraph({
                  children: [
                    new TextRun({
                      text: `Rabat, le ${nowDate}`,
                      size: 22,
                      color: "000000",
                    }),
                  ],
                  alignment: AlignmentType.LEFT,
                  spacing: { after: 400 },
                }),
                new Paragraph({
                  children: [
                    new TextRun({
                      text: "Objet : ",
                      bold: true,
                      size: 24,
                      color: "000000",
                    }),
                    new TextRun({
                      text: "Résiliation de domiciliation",
                      bold: true,
                      underline: { type: "single", color: "000000" },
                      size: 24,
                      color: "000000",
                    }),
                  ],
                  alignment: AlignmentType.RIGHT,
                  spacing: { after: 400 },
                }),
                new Paragraph({
                  children: [
                    new TextRun({
                      text: `En date du `,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: dateCreation,
                      bold: true,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: `, nous avons mis à la disposition de la société `,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: client?.nom_francais || client?.nom || "MAEXPERTISE CONSULTING",
                      bold: true,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: `, gratuitement une attestation de domiciliation sur notre adresse suivante : `,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: societe?.adresse_siege_social_francais || "44, AVENUE DE FRANCE, 2ÈME ETAGE, APPRT N 16, AGDAL-RABAT",
                      bold: true,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: `.`,
                      size: 22,
                      color: "000000",
                    }),
                  ],
                  alignment: AlignmentType.RIGHT,
                  spacing: { after: 300, line: 360, lineRule: "auto" },
                }),
                new Paragraph({
                  children: [
                    new TextRun({
                      text: `Suite à la décision de liquidation sociale de la société `,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: client?.nom_francais || client?.nom || "Ducimus provident",
                      bold: true,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: `, nous décidons de mettre fin à cette domiciliation.`,
                      size: 22,
                      color: "000000",
                    }),
                  ],
                  alignment: AlignmentType.RIGHT,
                  spacing: { after: 300, line: 360, lineRule: "auto" },
                }),
                new Paragraph({
                  children: [
                    new TextRun({
                      text: `Cette résiliation prendra effet à compter du `,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: dateFin,
                      bold: true,
                      size: 22,
                      color: "000000",
                    }),
                    new TextRun({
                      text: `.`,
                      size: 22,
                      color: "000000",
                    }),
                  ],
                  alignment: AlignmentType.RIGHT,
                  spacing: { after: 300, line: 360, lineRule: "auto" },
                }),
                new Paragraph({
                  children: [
                    new TextRun({
                      text: "Veuillez agréer, Madame, Monsieur, l'expression de nos salutations distinguées.",
                      size: 22,
                      color: "000000",
                    }),
                  ],
                  alignment: AlignmentType.RIGHT,
                  spacing: { after: 600, line: 360, lineRule: "auto" },
                }),
                new Paragraph({
                  children: [
                    new TextRun({
                      text: "Signature :",
                      size: 24,
                      bold: true,
                      color: "000000",
                    }),
                  ],
                  alignment: AlignmentType.CENTER,
                  spacing: { before: 400, after: 200 },
                }),
                new Paragraph({
                  text: "",
                  spacing: { after: 600 },
                }),
                new Paragraph({
                  children: [
                    new TextRun({
                      text: societe?.president_nom || societe?.nom_complet_francais || "MOURAD EL MANSOURI",
                      bold: true,
                      size: 24,
                      color: "000000",
                    }),
                  ],
                  alignment: AlignmentType.CENTER,
                  spacing: { before: 200 },
                }),
                new Paragraph({
                  text: "",
                  spacing: { after: 400 },
                }),
              ],
              verticalAlign: "center",
            },
          ],
        });

        // Generate and download the Word file
        const blob = await Packer.toBlob(doc);
        const companyName = client?.nom_francais || client?.nom || societe?.societe_nom || "contract";
        const fileName = `Resiliation_Domiciliation_${companyName.replace(/[^a-zA-Z0-9]/g, '_')}_${nowDate.replace(/\//g, '-')}.docx`;

        saveAs(blob, fileName);
      } catch (error) {
        this.showErrorMessage("Erreur lors de la génération du contrat Word.");
      }
    },

    // ==================== CRUD Operations ====================
    async onDeleteResiliation(record) {
      const result = await Swal.fire({
        title: "Êtes-vous sûr(e) ?",
        text: "Cette résiliation sera définitivement supprimée !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui, supprimer !",
        cancelButtonText: "Annuler",
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/resiliations/${record.id}`, {
            headers: this.getApiHeaders(),
          });

          this.showSuccessMessage("Résiliation supprimée avec succès");
          await this.fetchResiliations();
        } catch (error) {
          console.error("Erreur lors de la suppression:", error);
          this.handleApiError(error);
        }
      }
    },

    // ==================== Data Fetching ====================
    async fetchResiliations() {
      try {
        const res = await axios.get("/resiliations", {
          headers: {
            Authorization: `Bearer ${localStorage.getItem("access_token")}`,
          },
        });
        this.resiliations = res.data;
      } catch (error) {
        console.error("Erreur de chargement des résiliations:", error);
        this.showErrorMessage("Impossible de charger les résiliations");
      }
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
  },
  async mounted() {
    await this.fetchCompanyInfo();
    await this.fetchResiliations();
  },
};
</script>

<style scoped>
/* ==================== Modern Header Styles (copié de DomiciliationRenewHistory.vue) ==================== */
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

/* ==================== Filter Card Styles (copié de DomiciliationRenewHistory.vue) ==================== */
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

.reference-cell {
  font-weight: 600;
}



.date-cell {
  color: #6c757d;
  font-size: 0.875rem;
}

.status-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  color: white;
}

.comments-cell {
  max-width: 200px;
  word-wrap: break-word;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
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

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

/* ==================== Pagination Styles (copié de DomiciliationRenewHistory.vue) ==================== */
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

.pagination-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-color: #667eea;
  color: white;
  transform: translateY(-2px);
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

  .action-buttons {
    flex-direction: column;
    gap: 0.25rem;
  }

  .comments-cell {
    max-width: 100%;
  }
}
</style>
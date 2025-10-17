<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <!-- Enhanced Header Section - Style copié de Resiliations.vue -->
      <div class="page-header modern-header" style="background-color: #ABC270;">
        <div class="header-content">
          <div class="page-title-section">
            <div class="title-wrapper">
              <div class="icon-wrapper">
                <i class="fas fa-users text-primary"></i>
              </div>
              <div class="title-content">
                <h4 class="page-title">Gestion des utilisateurs</h4>
                <p class="page-subtitle">Gérez vos utilisateurs</p>
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
            <div class="page-btn">
              <a href="javascript:void(0);" class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-user">
                <VueFeather type="plus-circle" class="me-2" />
                Ajouter Nouvel Utilisateur
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Enhanced Filter Card - Style copié de Resiliations.vue -->
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
                  @input="filterUsers" />
                <i class="fas fa-search search-icon"></i>
              </div>
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-user me-1"></i>
                Nom
              </label>
              <input type="text" class="form-control" placeholder="Filtrer par nom..." v-model="filters.nom" @input="filterUsers" />
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-envelope me-1"></i>
                Email
              </label>
              <input type="text" class="form-control" placeholder="Filtrer par email..." v-model="filters.email" @input="filterUsers" />
            </div>

            <div class="filter-group">
              <label class="filter-label">
                <i class="fas fa-user-tag me-1"></i>
                Rôle
              </label>
              <select class="form-select" v-model="filters.role" @change="filterUsers">
                <option value="">Tous les rôles</option>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
              </select>
            </div>
          </div>
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
                      <i class="fas fa-user me-1"></i>
                      Nom
                    </th>
                    <th>
                      <i class="fas fa-user me-1"></i>
                      Prénom
                    </th>
                    <th>
                      <i class="fas fa-envelope me-1"></i>
                      Email
                    </th>
                    <th>
                      <i class="fas fa-phone me-1"></i>
                      Téléphone
                    </th>
                    <th>
                      <i class="fas fa-map-marker-alt me-1"></i>
                      Adresse
                    </th>
                    <th>
                      <i class="fas fa-city me-1"></i>
                      Ville
                    </th>
                    <th>
                      <i class="fas fa-user-tag me-1"></i>
                      Rôle
                    </th>
                    <th>
                      <i class="fas fa-cogs me-1"></i>
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="record in filteredData" :key="record.id" class="table-row">
                    <td class="user-name-cell">
                      <span class="fw-semibold">{{ record.Nom }}</span>
                    </td>
                    <td class="text-nowrap">{{ record.Prenom }}</td>
                    <td class="email-cell">
                      <a :href="`mailto:${record.Email}`" class="text-primary">
                        {{ record.Email }}
                      </a>
                    </td>
                    <td class="phone-cell">
                      <a :href="`tel:${record.Telephone}`" class="text-success">
                        {{ record.Telephone }}
                      </a>
                    </td>
                    <td class="address-cell">
                      <div class="text-nowrap text-truncate" style="max-width: 200px;" :title="record.Adresse">
                        {{ record.Adresse }}
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-light text-dark">{{ record.Ville }}</span>
                    </td>
                    <td>
                      <span :class="record.Role === 'Admin' ? 'badge bg-warning' : 'badge bg-primary'">
                        {{ record.Role }}
                      </span>
                    </td>
                    <td>
                      <div class="action-buttons">
                        <button class="action-btn edit-action" data-bs-toggle="modal" data-bs-target="#edit-user"
                          title="Modifier" @click.prevent="onEditUser(record)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete-action" @click.prevent="onDeleteUser(record)" title="Supprimer">
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
                  sur <strong>{{ allFilteredData.length }}</strong> utilisateurs
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
  <users-modal ref="usersModal" @user-added="onUserAdded" @user-edited="fetchUsers"></users-modal>
</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import Swal from "sweetalert2";

export default {
  data() {
    return {
      allUsers: [],
         companyInfo: null,
      search: "",
      filters: {
        nom: "",
        email: "",
        role: "",
      },
      currentUserEmail: "",
      pageSize: 10,
      currentPage: 1,
    };
  },
  computed: {
    filteredData() {
      let filtered = [...this.allUsers];
      
      // General search
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (u) =>
            (u.Nom && u.Nom.toLowerCase().includes(s)) ||
            (u.Prenom && u.Prenom.toLowerCase().includes(s)) ||
            (u.Email && u.Email.toLowerCase().includes(s)) ||
            (u.Telephone && u.Telephone.toLowerCase().includes(s)) ||
            (u.Adresse && u.Adresse.toLowerCase().includes(s)) ||
            (u.Ville && u.Ville.toLowerCase().includes(s)) ||
            (u.Role && u.Role.toLowerCase().includes(s))
        );
      }

      // Specific filters
      if (this.filters.nom) {
        filtered = filtered.filter(u =>
          u.Nom && u.Nom.toLowerCase().includes(this.filters.nom.toLowerCase())
        );
      }

      if (this.filters.email) {
        filtered = filtered.filter(u =>
          u.Email && u.Email.toLowerCase().includes(this.filters.email.toLowerCase())
        );
      }

      if (this.filters.role) {
        filtered = filtered.filter(u => u.Role === this.filters.role);
      }

      // Appliquer la pagination manuellement
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return filtered.slice(start, end);
    },

    // Données complètes pour le calcul de pagination
    allFilteredData() {
      let filtered = [...this.allUsers];
      
      // General search
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (u) =>
            (u.Nom && u.Nom.toLowerCase().includes(s)) ||
            (u.Prenom && u.Prenom.toLowerCase().includes(s)) ||
            (u.Email && u.Email.toLowerCase().includes(s)) ||
            (u.Telephone && u.Telephone.toLowerCase().includes(s)) ||
            (u.Adresse && u.Adresse.toLowerCase().includes(s)) ||
            (u.Ville && u.Ville.toLowerCase().includes(s)) ||
            (u.Role && u.Role.toLowerCase().includes(s))
        );
      }

      // Specific filters
      if (this.filters.nom) {
        filtered = filtered.filter(u =>
          u.Nom && u.Nom.toLowerCase().includes(this.filters.nom.toLowerCase())
        );
      }

      if (this.filters.email) {
        filtered = filtered.filter(u =>
          u.Email && u.Email.toLowerCase().includes(this.filters.email.toLowerCase())
        );
      }

      if (this.filters.role) {
        filtered = filtered.filter(u => u.Role === this.filters.role);
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
    // ==================== Filter & Pagination ====================
    clearFilters() {
      this.search = "";
      this.filters = {
        nom: "",
        email: "",
        role: "",
      };
      this.currentPage = 1;
    },

    filterUsers() {
      this.currentPage = 1;
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
// Fetch company information (à ajouter dans vos méthodes)
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
    } else {
      this.companyInfo = null;
    }
  } catch (err) {
    this.companyInfo = null;
  }
},

// Get image URL for Laravel Storage (à ajouter dans vos méthodes)
getImageUrl(path) {
  if (!path) return null;
  return `http://localhost:8000/storage/${path}`;
},
    // ==================== Export Methods ====================
    exportToExcel() {
      const dataToExport = this.allFilteredData.map((user) => ({
        Nom: user.Nom,
        Prénom: user.Prenom,
        Email: user.Email,
        Téléphone: user.Telephone,
        Adresse: user.Adresse,
        Ville: user.Ville,
        Rôle: user.Role,
      }));

      const worksheet = XLSX.utils.json_to_sheet(dataToExport);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "Utilisateurs");

      const excelBuffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });
      const data = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      saveAs(
        data,
        `utilisateurs_${new Date().toISOString().split("T")[0]}.xlsx`
      );
    },
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
  printWindow.document.title = "Liste des utilisateurs";
  printWindow.focus();
},

generatePrintableHTML() {
  const currentDate = new Date();
  const formattedDate = currentDate.toLocaleDateString('fr-FR');
  const formattedTime = currentDate.toLocaleTimeString('fr-FR');

  return `<!DOCTYPE html>
<html>
  <head>
    <title>Liste des utilisateurs</title>
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
      
      .role-badge {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 8px;
        font-weight: bold;
      }
      
      .role-admin {
        background-color: #ffc107;
        color: #000;
      }
      
      .role-user {
        background-color: #0d6efd;
        color: white;
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
          <div class="title">Liste des Utilisateurs</div>
          <div class="date">Généré le ${formattedDate} à ${formattedTime}</div>
        </div>
      </div>
      
      <table>
        <thead>
          <tr>
            <th style="width: 15%;">Nom</th>
            <th style="width: 15%;">Prénom</th>
            <th style="width: 20%;">Email</th>
            <th style="width: 15%;">Téléphone</th>
            <th style="width: 15%;">Adresse</th>
            <th style="width: 10%;">Ville</th>
            <th style="width: 10%;">Rôle</th>
          </tr>
        </thead>
        <tbody>
          ${this.allFilteredData
          .map(
            (user) => `
            <tr>
              <td>${user.Nom || '-'}</td>
              <td>${user.Prenom || '-'}</td>
              <td>${user.Email || 'N/A'}</td>
              <td>${user.Telephone || 'N/A'}</td>
              <td>${user.Adresse || 'N/A'}</td>
              <td>${user.Ville || 'N/A'}</td>
              <td class="text-center">
                <span class="role-badge ${user.Role === 'Admin' ? 'role-admin' : 'role-user'}">
                  ${user.Role || 'N/A'}
                </span>
              </td>
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
        document.title = "Liste des utilisateurs";
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
  doc.text("Liste des utilisateurs", 14, 20);
  doc.setFontSize(11);
  doc.text(`Exporté le: ${new Date().toLocaleDateString('fr-FR')}`, 14, 30);

  doc.autoTable({
    head: [["Nom", "Prénom", "Email", "Téléphone", "Adresse", "Ville", "Rôle"]],
    body: this.allFilteredData.map((user) => [
      user.Nom || '-',
      user.Prenom || '-',
      user.Email || 'N/A',
      user.Telephone || 'N/A',
      user.Adresse || 'N/A',
      user.Ville || 'N/A',
      user.Role || 'N/A'
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

  doc.save(`utilisateurs_${new Date().toISOString().split("T")[0]}.pdf`);
},

    printTable() {
      const printWindow = window.open("", "_blank");
      const tableHTML = `
        <!DOCTYPE html>
        <html>
          <head>
            <title>Liste des utilisateurs</title>
            <meta charset="UTF-8">
            <style>
              body { 
                font-family: Arial, sans-serif;
                margin: 20px;
              }
              table { 
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
              }
              th, td { 
                border: 1px solid #ddd;
                padding: 12px 8px;
                text-align: left;
              }
              th { 
                background-color: #f5f5f5;
                font-weight: bold;
              }
              .header { 
                margin-bottom: 30px;
              }
              .date { 
                color: #666;
                margin: 10px 0 20px;
              }
              @media print {
                .no-print { 
                  display: none; 
                }
                body { 
                  margin: 0;
                  padding: 15px;
                }
              }
            </style>
          </head>
          <body>
            <div class="header">
              <h1>Liste des utilisateurs</h1>
              <div class="date">Date d'impression: ${new Date().toLocaleDateString()}</div>
            </div>
            <table>
              <thead>
                <tr>
                  <th>Nom</th>
                  <th>Prénom</th>
                  <th>Email</th>
                  <th>Téléphone</th>
                  <th>Adresse</th>
                  <th>Ville</th>
                  <th>Rôle</th>
                </tr>
              </thead>
              <tbody>
                ${this.allFilteredData
          .map(
            (user) => `
                  <tr>
                    <td>${user.Nom}</td>
                    <td>${user.Prenom}</td>
                    <td>${user.Email}</td>
                    <td>${user.Telephone}</td>
                    <td>${user.Adresse}</td>
                    <td>${user.Ville}</td>
                    <td>${user.Role}</td>
                  </tr>
                `
          )
          .join("")}
              </tbody>
            </table>
            <div class="no-print" style="margin-top: 30px;">
              <button onclick="window.print()" style="padding: 10px 20px; margin-right: 10px;">
                Imprimer
              </button>
              <button onclick="window.close()" style="padding: 10px 20px;">
                Fermer
              </button>
            </div>
          </body>
        </html>
      `;
      printWindow.document.write(tableHTML);
      printWindow.document.close();
    },

    // ==================== User Management ====================
    getCurrentUserEmail() {
      try {
        const user = JSON.parse(localStorage.getItem('user'));
        this.currentUserEmail = user?.email || '';
      } catch (error) {
        console.warn('Unable to get user email from localStorage:', error);
        this.currentUserEmail = '';
      }
    },

    async fetchUsers() {
      try {
        const [gestionnairesRes, usersRes] = await Promise.all([
          axios.get("/gestionnaires"),
          axios.get("/users"),
        ]);
        const gestionnaires = gestionnairesRes.data;
        const users = usersRes.data;

        console.log("Gestionnaires:", gestionnaires);
        console.log("Users:", users);

        this.allUsers = gestionnaires.map((g) => {
          const user = users.find((u) => u.id === g.user_id) || {};
          return {
            id: g.id,
            Nom: g.nom || (user.name ? user.name.split(" ")[0] : ""),
            Prenom:
              g.prenom ||
              (user.name ? user.name.split(" ").slice(1).join(" ") : ""),
            Email: g.email || user.email || this.currentUserEmail,
            Telephone: g.telephone || user.telephone,
            Adresse: g.adresse || user.adresse,
            Ville: g.ville || user.ville,
            Role: user.is_admin === 1 ? "Admin" : "User",
            user_id: g.user_id,
            is_admin: user.is_admin,
          };
        });
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text: "Impossible de charger les utilisateurs.",
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 2000,
        });
      }
    },

    onEditUser(record) {
      this.$refs.usersModal.openEditModal({
        id: record.id,
        nom: record.Nom,
        prenom: record.Prenom,
        email: record.Email,
        telephone: record.Telephone,
        adresse: record.Adresse,
        ville: record.Ville,
        user_id: record.user_id,
        is_admin: record.is_admin,
      });
    },

    async onDeleteUser(record) {
      const result = await Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Cette action supprimera l'utilisateur définitivement.",
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
          await axios.delete(`/users/${record.id}`);
          this.fetchUsers();
          Swal.fire({
            icon: "success",
            title: "Succès",
            text: "Utilisateur supprimé avec succès",
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
              "Échec de la suppression de l'utilisateur.",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
          });
        }
      }
    },

    onUserAdded() {
      this.fetchUsers();
      Swal.fire({
        icon: "success",
        title: "Succès",
        text: "Utilisateur créé avec succès",
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 2000,
      });
    },
  },

  mounted() {
    this.getCurrentUserEmail();
    this.fetchCompanyInfo();
    this.fetchUsers();
  },
};
</script>

<style scoped>
/* ==================== Modern Header Styles (copié de Resiliations.vue) ==================== */
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

.btn-added {
  background: rgba(255, 255, 255, 0.9);
  color: #ABC270;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-added:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* ==================== Filter Card Styles (copié de Resiliations.vue) ==================== */
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

.user-name-cell {
  font-weight: 600;
}

.email-cell a {
  text-decoration: none;
}

.email-cell a:hover {
  text-decoration: underline;
}

.phone-cell a {
  text-decoration: none;
}

.phone-cell a:hover {
  text-decoration: underline;
}

.address-cell {
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

.edit-action {
  background: linear-gradient(135deg, #ABC270, #ABC270);
  color: white;
}

.edit-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.delete-action {
  background: linear-gradient(135deg, #dc3545, #c82333);
  color: white;
}

.delete-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

/* ==================== Pagination Styles (copié de Resiliations.vue) ==================== */
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

  .address-cell {
    max-width: 100%;
  }
}

/* Espacement pour les boutons SweetAlert */
:deep(.swal-actions-spaced) {
  gap: 1.5rem !important;
  justify-content: center !important;
}

:deep(.swal-actions-spaced .swal2-styled) {
  margin: 0 0.75rem !important;
}
</style>
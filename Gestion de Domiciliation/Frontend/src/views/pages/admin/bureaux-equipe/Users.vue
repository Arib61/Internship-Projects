<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <div class="page-header">
        <div class="add-item d-flex">
          <div class="page-title">
            <h4>Gestion des utilisateurs</h4>
            <h6>Gérez vos utilisateurs</h6>
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
          <li>
            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" title="Imprimer"
              @click="printTable">
              <VueFeather type="printer" />
            </a>
          </li>
        </ul>
        <div class="page-btn">
          <a href="javascript:void(0);"  class="btn btn-added" data-bs-toggle="modal" data-bs-target="#add-user">
            <VueFeather type="plus-circle" class="me-2" />
            Ajouter Nouvel Utilisateur
          </a>
        </div>
      </div>

      <div class="card table-list-card">
        <div class="card-body">
          <div class="table-top">
            <div class="search-set">
              <div class="search-input">
                <input type="text" class="dark-input" placeholder="Rechercher..." v-model="search"
                  @input="filterUsers" />
                <a href="" class="btn btn-searchset">
                  <VueFeather type="search" />
                </a>
              </div>
            </div>
          </div>



          <!-- Table Section -->
          <div class="table-responsive">
            <a-table class="table datanew" :columns="columns" :data-source="filteredData" :row-selection="{}"
              :pagination="false">
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'Nom'">
                  <div class="text-nowrap user-name-cell">
                    <span class="fw-semibold">{{ record.Nom }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'Prenom'">
                  <div class="text-nowrap">{{ record.Prenom }}</div>
                </template>
                <template v-else-if="column.key === 'Email'">
                  <div class="text-nowrap email-cell">
                    <a :href="`mailto:${record.Email}`" class="text-primary">
                      {{ record.Email }}
                    </a>
                  </div>
                </template>
                <template v-else-if="column.key === 'Telephone'">
                  <div class="text-nowrap phone-cell">
                    <a :href="`tel:${record.Telephone}`" class="text-success">
                      {{ record.Telephone }}
                    </a>
                  </div>
                </template>
                <template v-else-if="column.key === 'Adresse'">
                  <div class="text-nowrap text-truncate address-cell" style="max-width: 200px;" :title="record.Adresse">
                    {{ record.Adresse }}
                  </div>
                </template>
                <template v-else-if="column.key === 'Ville'">
                  <div class="text-nowrap">
                    <span class="badge bg-light text-dark">{{ record.Ville }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'Role'">
                  <div class="text-nowrap">
                    <span :class="record.Role === 'Admin' ? 'badge bg-warning' : 'badge bg-primary'">
                      {{ record.Role }}
                    </span>
                  </div>
                </template>
                <template v-else-if="column.key === 'action'">
                  <td class="action-table-data">
                    <div class="edit-delete-action">
                      <a class="me-2 p-2 mb-0 action-btn edit-btn" data-bs-toggle="modal" data-bs-target="#edit-user"
                        title="Modifier" @click.prevent="onEditUser(record)">
                        <i data-feather="edit" class="feather-edit"></i>
                      </a>
                      <a class="me-2 confirm-text p-2 mb-0 action-btn delete-btn" href="javascript:void(0);"
                        data-bs-toggle="tooltip" title="Supprimer" @click.prevent="onDeleteUser(record)">
                        <i data-feather="trash-2" class="feather-trash-2"></i>
                      </a>
                    </div>
                  </td>
                </template>
              </template>
            </a-table>
          </div>

          <!-- Pagination personnalisée au bas -->
          <div class="custom-pagination-wrapper d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <!-- Contrôle de taille de page à gauche -->
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

            <!-- Informations de pagination au centre -->
            <div class="pagination-info text-muted">
              Affichage de {{ startRecord }} à {{ endRecord }} sur {{ allFilteredData.length }} entrées
            </div>

            <!-- Navigation de page à droite -->
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

                <!-- Sélecteur de page -->
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
  <users-modal ref="usersModal" @user-added="onUserAdded" @user-edited="fetchUsers"></users-modal>
</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import Swal from "sweetalert2";

const columns = [
  {
    title: "Nom",
    dataIndex: "Nom",
    key: "Nom",
    sorter: {
      compare: (a, b) => a.Nom.localeCompare(b.Nom),
    },
  },
  {
    title: "Prénom",
    dataIndex: "Prenom",
    key: "Prenom",
    sorter: {
      compare: (a, b) => a.Prenom.localeCompare(b.Prenom),
    },
  },
  {
    title: "Email",
    dataIndex: "Email",
    key: "Email",
    sorter: {
      compare: (a, b) => a.Email.localeCompare(b.Email),
    },
  },
  {
    title: "Téléphone",
    dataIndex: "Telephone",
    key: "Telephone",
    sorter: {
      compare: (a, b) => (a.Telephone || "").localeCompare(b.Telephone || ""),
    },
  },
  {
    title: "Adresse",
    dataIndex: "Adresse",
    key: "Adresse",
    sorter: {
      compare: (a, b) => (a.Adresse || "").localeCompare(b.Adresse || ""),
    },
  },
  {
    title: "Ville",
    dataIndex: "Ville",
    key: "Ville",
    sorter: {
      compare: (a, b) => (a.Ville || "").localeCompare(b.Ville || ""),
    },
  },
  {
    title: "Rôle",
    dataIndex: "Role",
    key: "Role",
    sorter: {
      compare: (a, b) => a.Role.localeCompare(b.Role),
    },
  },
  {
    title: "Action",
    key: "action",
    sorter: false,
  },
];

export default {
  data() {
    return {
      columns,
      allUsers: [],
      search: "",
      currentUserEmail: "",
      pageSize: 10,
      currentPage: 1,
    };
  },
  computed: {
    filteredData() {
      let filtered = [...this.allUsers];
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

      // Appliquer la pagination manuellement
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return filtered.slice(start, end);
    },

    // Données complètes pour le calcul de pagination
    allFilteredData() {
      let filtered = [...this.allUsers];
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
      const doc = new jsPDF();
      doc.autoTable = autoTable.bind(null, doc);
      doc.setFontSize(18);
      doc.text("Liste des utilisateurs", 14, 20);
      doc.setFontSize(11);
      doc.text(`Exporté le: ${new Date().toLocaleDateString()}`, 14, 30);
      doc.autoTable({
        head: [["Nom", "Prénom", "Email", "Téléphone", "Adresse", "Ville", "Rôle"]],
        body: this.allFilteredData.map((user) => [
          user.Nom,
          user.Prenom,
          user.Email,
          user.Telephone,
          user.Adresse,
          user.Ville,
          user.Role,
        ]),
        startY: 40,
        styles: { fontSize: 10, font: "helvetica" },
        headStyles: {
          fillColor: [41, 128, 185],
          textColor: 255,
          fontSize: 11,
          fontStyle: "bold",
        },
        alternateRowStyles: { fillColor: [245, 245, 245] },
        margin: { top: 40 },
      });
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

    toggleCollapse() {
      const collapseHeader = this.$refs.collapseHeader;
      if (collapseHeader) {
        collapseHeader.classList.toggle("active");
        document.body.classList.toggle("header-collapse");
      }
    },

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

    filterUsers() {
      this.currentPage = 1;
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
    this.fetchUsers();
  },
};
</script>

<style scoped>
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
/* CSS à ajouter dans votre section <style scoped> */

/* Espacement pour les boutons SweetAlert */
:deep(.swal-actions-spaced) {
  gap: 1.5rem !important;
  justify-content: center !important;
}

/* Alternative si la première méthode ne fonctionne pas */
:deep(.swal-actions-spaced .swal2-styled) {
  margin: 0 0.75rem !important;
}
}
</style>
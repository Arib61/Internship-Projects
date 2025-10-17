<template>
<layout-header></layout-header>
<layout-sidebar></layout-sidebar>
<div class="page-wrapper">
  <div class="content">
    <!-- Enhanced Header Section - Style moderne -->
    <div class="page-header modern-header" style="background-color: #ABC270;">
      <div class="header-content">
        <div class="page-title-section">
          <div class="title-wrapper">
            <div class="icon-wrapper">
              <i class="fas fa-users text-primary"></i>
            </div>
            <div class="title-content">
              <h4 class="page-title">Gestion des clients</h4>
              <p class="page-subtitle">Gérez vos clients</p>
            </div>
          </div>
        </div>

        <div class="header-actions">
          <div class="export-actions">
            <button class="action-btn export-btn" @click="exportToExcel" data-bs-toggle="tooltip"
              title="Exporter en Excel">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M14 2V8H20" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M8 13H16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M8 17H16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M10 9H9H8" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M12 17V13" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M15 17L17 13" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M17 17L15 13" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </button>
          </div>
          <div class="page-btn">
            <router-link to="/admin/addclient" class="btn btn-added">
              <VueFeather type="plus-circle" class="me-2" />
              Ajouter Nouveau Client
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Filter Card -->
    <div class="card filter-card">
      <div class="card-header">
        <h6 class="card-title mb-0">
          <i class="fas fa-filter me-2"></i>
          Filtres et Recherche
        </h6>
        <button class="btn btn-sm btn-outline-secondary" @click="resetFilters">
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
                @input="filterClients" />
              <i class="fas fa-search search-icon"></i>
            </div>
          </div>

          <div class="filter-group">
            <label class="filter-label">
              <i class="fas fa-font me-1"></i>
              Filtrer par lettre
            </label>
            <select v-model="selectedLetter" @change="applyFilters" class="form-select">
              <option value="">Toutes les lettres</option>
              <option v-for="letter in alphabetLetters" :key="letter" :value="letter">
                {{ letter }}
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">
              <i class="fas fa-map-marker-alt me-1"></i>
              Filtrer par ville
            </label>
            <select v-model="selectedCity" @change="applyFilters" class="form-select">
              <option value="">Toutes les villes</option>
              <option v-for="city in uniqueCitiesFromData" :key="city" :value="city">
                {{ city }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Data Table Card -->
    <div class="card data-table-card">
      <div class="card-body">
        <!-- Table Section with Horizontal Scroll -->
        <div class="table-container">
          <div class="table-responsive">
            <a-table
              class="table table-hover table-striped mb-0 modern-table"
              :columns="columns"
              :data-source="filteredData"
              :row-selection="{}"
              :pagination="false"
              :scroll="{ x: 'max-content' }"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'nom_francais'">
                  <div class="text-nowrap user-name-cell">
                    <span class="fw-semibold">{{ record.nom_francais }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'nom_arabe'">
                  <div class="text-nowrap">{{ record.nom_arabe }}</div>
                </template>
                <template v-else-if="column.key === 'adresse_siege_social_francais'">
                  <div class="text-nowrap text-truncate address-cell" style="max-width: 200px;" :title="record.adresse_siege_social_francais">
                    {{ record.adresse_siege_social_francais }}
                  </div>
                </template>
                <template v-else-if="column.key === 'adresse_siege_social_arabe'">
                  <div class="text-nowrap text-truncate address-cell" style="max-width: 200px;" :title="record.adresse_siege_social_arabe">
                    {{ record.adresse_siege_social_arabe }}
                  </div>
                </template>
                <template v-else-if="column.key === 'ice'">
                  <div class="text-nowrap">{{ record.ice }}</div>
                </template>
                <template v-else-if="column.key === 'cin'">
                  <div class="text-nowrap">{{ record.cin }}</div>
                </template>
                <template v-else-if="column.key === 'certificat_negative'">
                  <div class="text-nowrap text-truncate" style="max-width: 150px;" :title="record.certificat_negative">
                    {{ record.certificat_negative }}
                  </div>
                </template>
                <template v-else-if="column.key === 'rc'">
                  <div class="text-nowrap">{{ record.rc }}</div>
                </template>
                <template v-else-if="column.key === 'identifiant_fiscal'">
                  <div class="text-nowrap">{{ record.identifiant_fiscal }}</div>
                </template>
                <template v-else-if="column.key === 'tp'">
                  <div class="text-nowrap">{{ record.tp }}</div>
                </template>
                <template v-else-if="column.key === 'tribunal'">
                  <div class="text-nowrap text-truncate" style="max-width: 140px;" :title="record.tribunal">
                    {{ record.tribunal }}
                  </div>
                </template>
                <template v-else-if="column.key === 'type_tribunal'">
                  <div class="text-nowrap">{{ record.type_tribunal }}</div>
                </template>
                <template v-else-if="column.key === 'telephone'">
                  <div class="text-nowrap phone-cell">
                    <a :href="`tel:${record.telephone}`" class="text-success">
                      {{ record.telephone }}
                    </a>
                  </div>
                </template>
                <template v-else-if="column.key === 'email'">
                  <div class="text-nowrap email-cell">
                    <a :href="`mailto:${record.email}`" class="text-primary">
                      {{ record.email }}
                    </a>
                  </div>
                </template>
                <template v-else-if="column.key === 'pays'">
                  <div class="text-nowrap">
                    <span class="badge bg-info text-white">{{ record.pays }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'ville'">
                  <div class="text-nowrap">
                    <span class="badge bg-light text-dark">{{ record.ville }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'website'">
                  <div class="text-nowrap text-truncate" style="max-width: 180px;">
                    <a v-if="record.website" :href="record.website" target="_blank" class="text-info">
                      {{ record.website }}
                    </a>
                    <span v-else>-</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'capital_social'">
                  <div class="text-nowrap">
                    <span class="fw-semibold text-success">{{ formatCurrency(record.capital_social) }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'type_entreprise'">
                  <div class="text-nowrap">
                    <span class="badge bg-secondary">{{ record.type_entreprise }}</span>
                  </div>
                </template>
                <template v-else-if="column.key === 'type_client'">
                  <div class="text-nowrap">
                    <span :class="getTypeClientBadgeClass(record.type_client)">
                      {{ record.type_client }}
                    </span>
                  </div>
                </template>
                <template v-else-if="column.key === 'status'">
                  <div class="text-nowrap">
                    <span :class="record.status === 'ACTIVE' ? 'badge bg-success' : 'badge bg-danger'">
                      {{ record.status }}
                    </span>
                  </div>
                </template>
                <template v-else-if="column.key === 'action'">
                  <div class="action-buttons">
                    <router-link
                      :to="`/admin/clients/${record.id}`"
                      class="action-btn view-action"
                      title="Voir les détails"
                    >
                      <i class="fas fa-eye"></i>
                    </router-link>

                    <router-link
                      :to="{ name: 'EditClient', params: { id: record.id } }"
                      class="action-btn edit-action"
                      title="Modifier"
                    >
                      <i class="fas fa-edit"></i>
                    </router-link>
                    
                    <button class="action-btn delete-action" @click.prevent="onDeleteClient(record)" title="Supprimer">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </template>
              </template>
            </a-table>
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
                sur <strong>{{ allFilteredData.length }}</strong> clients
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
<clients-modal ref="clientsModal" @client-added="onClientAdded" @client-edited="fetchClients"></clients-modal>
</template>

<script>
import axios from "axios";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import Swal from "sweetalert2";

const columns = [
  {
    title: "Nom (Français)",
    dataIndex: "nom_francais",
    key: "nom_francais",
    width: 180,
    fixed: 'left',
    sorter: {
      compare: (a, b) => (a.nom_francais || "").localeCompare(b.nom_francais || ""),
    },
  },
  {
    title: "ICE",
    dataIndex: "ice",
    key: "ice",
    width: 120,
    sorter: {
      compare: (a, b) => (a.ice || "").localeCompare(b.ice || ""),
    },
  },
  {
    title: "RC",
    dataIndex: "rc",
    key: "rc",
    width: 120,
    sorter: {
      compare: (a, b) => (a.rc || "").localeCompare(b.rc || ""),
    },
  },
  {
    title: "Identifiant Fiscal",
    dataIndex: "identifiant_fiscal",
    key: "identifiant_fiscal",
    width: 150,
    sorter: {
      compare: (a, b) => (a.identifiant_fiscal || "").localeCompare(b.identifiant_fiscal || ""),
    },
  },
  {
    title: "TP",
    dataIndex: "tp",
    key: "tp",
    width: 100,
    sorter: {
      compare: (a, b) => (a.tp || "").localeCompare(b.tp || ""),
    },
  },
  {
    title: "Téléphone",
    dataIndex: "telephone",
    key: "telephone",
    width: 140,
    sorter: {
      compare: (a, b) => (a.telephone || "").localeCompare(b.telephone || ""),
    },
  },
  {
    title: "Email",
    dataIndex: "email",
    key: "email",
    width: 200,
    ellipsis: true,
    sorter: {
      compare: (a, b) => (a.email || "").localeCompare(b.email || ""),
    },
  },
  {
    title: "Ville",
    dataIndex: "ville",
    key: "ville",
    width: 120,
    sorter: {
      compare: (a, b) => (a.ville || "").localeCompare(b.ville || ""),
    },
  },
  {
    title: "Site Web",
    dataIndex: "website",
    key: "website",
    width: 180,
    ellipsis: true,
    sorter: {
      compare: (a, b) => (a.website || "").localeCompare(b.website || ""),
    },
  },
  {
    title: "Capital Social",
    dataIndex: "capital_social",
    key: "capital_social",
    width: 130,
    sorter: {
      compare: (a, b) => (a.capital_social || 0) - (b.capital_social || 0),
    },
  },
  {
    title: "Type Entreprise",
    dataIndex: "type_entreprise",
    key: "type_entreprise",
    width: 140,
    sorter: {
      compare: (a, b) => (a.type_entreprise || "").localeCompare(b.type_entreprise || ""),
    },
  },
  {
    title: "Type Client",
    dataIndex: "type_client",
    key: "type_client",
    width: 120,
    sorter: {
      compare: (a, b) => (a.type_client || "").localeCompare(b.type_client || ""),
    },
  },
  {
    title: "Statut",
    dataIndex: "status",
    key: "status",
    width: 100,
    sorter: {
      compare: (a, b) => (a.status || "").localeCompare(b.status || ""),
    },
  },
  {
    title: "Action",
    key: "action",
    width: 150,
    fixed: 'right',
    sorter: false,
  },
];

export default {
  data() {
    return {
      columns,
      allClients: [],
      search: "",
      pageSize: 10,
      currentPage: 1,
      selectedLetter: "",
      selectedCity: "",
      alphabetLetters: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']
    };
  },
  computed: {
    filteredData() {
      let filtered = [...this.allClients];
      
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (c) =>
            (c.nom_francais && c.nom_francais.toLowerCase().includes(s)) ||
            (c.nom_arabe && c.nom_arabe.toLowerCase().includes(s)) ||
            (c.ice && c.ice.toLowerCase().includes(s)) ||
            (c.cin && c.cin.toLowerCase().includes(s)) ||
            (c.rc && c.rc.toLowerCase().includes(s)) ||
            (c.email && c.email.toLowerCase().includes(s)) ||
            (c.telephone && c.telephone.toLowerCase().includes(s)) ||
            (c.ville && c.ville.toLowerCase().includes(s)) ||
            (c.pays && c.pays.toLowerCase().includes(s)) ||
            (c.type_client && c.type_client.toLowerCase().includes(s)) ||
            (c.type_entreprise && c.type_entreprise.toLowerCase().includes(s)) ||
            (c.status && c.status.toLowerCase().includes(s))
        );
      }

      if (this.selectedLetter) {
        filtered = filtered.filter(c => 
          c.nom_francais && c.nom_francais.toUpperCase().startsWith(this.selectedLetter)
        );
      }

      if (this.selectedCity) {
        filtered = filtered.filter(c => 
          c.ville && c.ville === this.selectedCity
        );
      }

      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return filtered.slice(start, end);
    },

    allFilteredData() {
      let filtered = [...this.allClients];
      
      const s = this.search.toLowerCase().trim();
      if (s) {
        filtered = filtered.filter(
          (c) =>
            (c.nom_francais && c.nom_francais.toLowerCase().includes(s)) ||
            (c.nom_arabe && c.nom_arabe.toLowerCase().includes(s)) ||
            (c.ice && c.ice.toLowerCase().includes(s)) ||
            (c.cin && c.cin.toLowerCase().includes(s)) ||
            (c.rc && c.rc.toLowerCase().includes(s)) ||
            (c.email && c.email.toLowerCase().includes(s)) ||
            (c.telephone && c.telephone.toLowerCase().includes(s)) ||
            (c.ville && c.ville.toLowerCase().includes(s)) ||
            (c.pays && c.pays.toLowerCase().includes(s)) ||
            (c.type_client && c.type_client.toLowerCase().includes(s)) ||
            (c.type_entreprise && c.type_entreprise.toLowerCase().includes(s)) ||
            (c.status && c.status.toLowerCase().includes(s))
        );
      }

      if (this.selectedLetter) {
        filtered = filtered.filter(c => 
          c.nom_francais && c.nom_francais.toUpperCase().startsWith(this.selectedLetter)
        );
      }

      if (this.selectedCity) {
        filtered = filtered.filter(c => 
          c.ville && c.ville === this.selectedCity
        );
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

    uniqueCitiesFromData() {
      const cities = this.allClients
        .map(client => client.ville)
        .filter(ville => ville && ville.trim() !== '')
        .filter((ville, index, array) => array.indexOf(ville) === index)
        .sort();
      return cities;
    },
  },
  methods: {
    formatCurrency(amount) {
      if (!amount) return '0 DH';
      return new Intl.NumberFormat('fr-MA', {
        style: 'currency',
        currency: 'MAD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(amount).replace('MAD', 'DH');
    },

    getTypeClientBadgeClass(type) {
      const typeMap = {
        'Premium': 'badge bg-warning text-dark',
        'VIP': 'badge bg-danger text-white',
        'Standard': 'badge bg-primary text-white',
        'Enterprise': 'badge bg-success text-white',
      };
      return typeMap[type] || 'badge bg-secondary text-white';
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

    applyFilters() {
      this.currentPage = 1;
    },

    resetFilters() {
      this.selectedLetter = "";
      this.selectedCity = "";
      this.search = "";
      this.currentPage = 1;
    },

    filterClients() {
      this.currentPage = 1;
    },

    exportToExcel() {
      const dataToExport = this.allFilteredData.map((client) => ({
        "Nom en Français": client.nom_francais,
        "Nom en Arabe": client.nom_arabe,
        "Adresse en Français": client.adresse_siege_social_francais,
        "Adresse en Arabe": client.adresse_siege_social_arabe,
        "ICE": client.ice,
        "CIN": client.cin,
        "Certificat Négative": client.certificat_negative,
        "RC": client.rc,
        "Identifiant Fiscal": client.identifiant_fiscal,
        "TP": client.tp,
        "Tribunal": client.tribunal,
        "Type Tribunal": client.type_tribunal,
        "Téléphone": client.telephone,
        "Email": client.email,
        "Pays": client.pays,
        "Ville": client.ville,
        "Site Web": client.website,
        "Capital Social": client.capital_social,
        "Type Entreprise": client.type_entreprise,
        "Type Client": client.type_client,
      }));

      const worksheet = XLSX.utils.json_to_sheet(dataToExport);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "Clients");

      const excelBuffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });
      const data = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      saveAs(
        data,
        `clients_${new Date().toISOString().split("T")[0]}.xlsx`
      );
    },

    async fetchClients() {
      try {
        const response = await axios.get("/clients");
        this.allClients = response.data.map((c) => ({
          id: c.id,
          nom_francais: c.nom_francais || c.name || "",
          nom_arabe: c.nom_arabe || "",
          adresse_siege_social_francais: c.adresse_siege_social_francais || "",
          adresse_siege_social_arabe: c.adresse_siege_social_arabe || "",
          ice: c.ice || "",
          cin: c.cin || "",
          certificat_negative: c.certificat_negative || "",
          rc: c.rc || "",
          identifiant_fiscal: c.identifiant_fiscal || "",
          tp: c.tp || "",
          tribunal: c.tribunal || "",
          type_tribunal: c.type_tribunal || "",
          telephone: c.telephone || "",
          email: c.email || "",
          pays: c.pays || "",
          ville: c.ville || "",
          website: c.website || "",
          capital_social: c.capital_social || 0,
          type_entreprise: c.type_entreprise || "",
          type_client: c.type_client || "Standard",
          status: c.status || "INACTIVE",
          is_admin: c.is_admin,
          avatar: c.avatar || null,
        }));
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "Erreur",
          text: "Impossible de charger les clients.",
          toast: true,
          position: "top-end",
          showConfirmButton: false,
          timer: 2000,
        });
      }
    },

    onEditClient(record) {
      this.$refs.clientsModal.openEditModal(record);
    },

    async onDeleteClient(record) {
      const result = await Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Cette action supprimera le client définitivement.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Annuler",
        confirmButtonClass: "btn btn-danger me-3",
        cancelButtonClass: "btn btn-secondary",
        buttonsStyling: false,
        reverseButtons: true,
        customClass: {
          actions: 'swal-actions-spaced'
        }
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`/clients/${record.id}`);
          this.fetchClients();
          Swal.fire({
            icon: "success",
            title: "Succès",
            text: "Client supprimé avec succès",
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
              "Échec de la suppression du client.",
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 2000,
          });
        }
      }
    },

    onClientAdded() {
      this.fetchClients();
      Swal.fire({
        icon: "success",
        title: "Succès",
        text: "Client créé avec succès",
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 2000,
      });
    },
  },

  async mounted() {
    this.fetchClients();
  },
};
</script>

<style scoped>
/* ==================== Modern Header Styles ==================== */
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
text-decoration: none;
}

.btn-added:hover {
background: white;
transform: translateY(-2px);
box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
color: #ABC270;
}

/* ==================== Filter Card Styles ==================== */
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

:deep(.ant-table-thead > tr > th) {
background: linear-gradient(135deg, #f8f9fa, #e9ecef);
border-bottom: 2px solid #dee2e6;
font-weight: 600;
color: #495057;
padding: 1rem 0.75rem;
font-size: 0.875rem;
}

:deep(.ant-table-tbody > tr) {
transition: all 0.3s ease;
}

:deep(.ant-table-tbody > tr:hover) {
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
text-decoration: none;
}

.view-action {
background: linear-gradient(135deg, #ABC270, #ABC270);
color: white;
}

.view-action:hover {
transform: translateY(-2px);
box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
color: white;
}

.edit-action {
background: linear-gradient(135deg, #ABC270, #ABC270);
color: white;
}

.edit-action:hover {
transform: translateY(-2px);
box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
color: white;
}

.delete-action {
background: linear-gradient(135deg, #dc3545, #c82333);
color: white;
}

.delete-action:hover {
transform: translateY(-2px);
box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

/* ==================== Pagination Styles ==================== */
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

/* Badge styles */
.badge {
padding: 4px 8px;
border-radius: 4px;
font-size: 0.75rem;
font-weight: 500;
}

.bg-light {
background-color: #f8f9fa !important;
color: #6c757d !important;
}

.bg-success {
background-color: #28a745 !important;
color: white !important;
}

.bg-danger {
background-color: #dc3545 !important;
color: white !important;
}

.bg-primary {
background-color: #007bff !important;
color: white !important;
}

.bg-warning {
background-color: #ffc107 !important;
color: #212529 !important;
}

.bg-info {
background-color: #17a2b8 !important;
color: white !important;
}

.bg-secondary {
background-color: #6c757d !important;
color: white !important;
}
</style>
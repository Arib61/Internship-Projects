<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <div class="page-header">
        <div class="add-item d-flex">
          <div class="page-title">
            <h4>Gestion des clients</h4>
            <h6>Gérez vos clients</h6>
          </div>
        </div>
        <ul class="table-top-head">
          <li>
            <a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" title="Exporter en Excel"
              @click="exportToExcel">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#217346" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M14 2V8H20" stroke="#217346" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M8 13H16" stroke="#217346" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M8 17H16" stroke="#217346" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M10 9H9H8" stroke="#217346" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M12 17V13" stroke="#217346" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M15 17L17 13" stroke="#217346" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
                <path d="M17 17L15 13" stroke="#217346" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
              </svg>
            </a>
          </li>
        </ul>
        <div class="page-btn">
              <router-link to="/admin/addclient" class="btn btn-added">
            <VueFeather type="plus-circle" class="me-2" />
            Ajouter Nouveau Client
          </router-link>
        </div>
      </div>

      <div class="card table-list-card">
        <div class="card-body">
          <div class="table-top">
            <div class="search-filters-row d-flex align-items-center gap-3">
              <!-- Barre de recherche -->
              <div class="search-set">
                <div class="search-input">
                  <input type="text" class="dark-input" placeholder="Rechercher..." v-model="search"
                    @input="filterClients" />
                  <a href="" class="btn btn-searchset">
                    <VueFeather type="search" />
                  </a>
                </div>
              </div>

              <!-- Filtre par lettres -->
              <div class="filter-item">
                <select v-model="selectedLetter" @change="applyFilters" class="form-select filter-select">
                  <option value="">Filtrer par lettre</option>
                  <option v-for="letter in alphabetLetters" :key="letter" :value="letter">
                    {{ letter }}
                  </option>
                </select>
              </div>

              <!-- Filtre par ville -->
              <div class="filter-item">
                <select v-model="selectedCity" @change="applyFilters" class="form-select filter-select">
                  <option value="">Filtrer par ville</option>
                  <option v-for="city in uniqueCitiesFromData" :key="city" :value="city">
                    {{ city }}
                  </option>
                </select>
              </div>

              <!-- Bouton reset -->
              <div class="filter-item">
                <button @click="resetFilters" class="btn btn-outline-secondary reset-btn" title="Réinitialiser les filtres">
                  <VueFeather type="x" size="16" />
                </button>
              </div>
            </div>
          </div>

          <!-- Table Section with Horizontal Scroll -->
          <div class="table-responsive-wrapper">
            <div class="table-scroll-container">
              <a-table
                class="table datanew full-width-table"
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
                    <div class="action-table-data">
                      <div class="edit-delete-action">
                     
                     
                      <router-link
  :to="`/admin/clients/${record.id}`"
  class="me-2 p-2 mb-0 action-btn view-btn"
  title="Voir les détails"
>
  <i data-feather="eye" class="feather-eye"></i>
</router-link>

                       <!-- Bouton d'édition modifié pour naviguer vers la route EditClient -->
        <router-link
          :to="{ name: 'EditClient', params: { id: record.id } }"
          class="me-2 p-2 mb-0 action-btn edit-btn"
          title="Modifier"
        >
          <i data-feather="edit" class="feather-edit"></i>
        </router-link>
                        
                        <a class="me-2 confirm-text p-2 mb-0 action-btn delete-btn" href="javascript:void(0);"
                          data-bs-toggle="tooltip" title="Supprimer" @click.prevent="onDeleteClient(record)">
                          <i data-feather="trash-2" class="feather-trash-2"></i>
                        </a>
                      </div>
                    </div>
                  </template>
                </template>
              </a-table>
            </div>
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
      // Nouveaux filtres
      selectedLetter: "",
      selectedCity: "",
      alphabetLetters: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']
    };
  },
  computed: {
    filteredData() {
      let filtered = [...this.allClients];
      
      // Filtre de recherche global
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

      // Filtre par lettre
      if (this.selectedLetter) {
        filtered = filtered.filter(c => 
          c.nom_francais && c.nom_francais.toUpperCase().startsWith(this.selectedLetter)
        );
      }

      // Filtre par ville
      if (this.selectedCity) {
        filtered = filtered.filter(c => 
          c.ville && c.ville === this.selectedCity
        );
      }

      // Appliquer la pagination manuellement
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return filtered.slice(start, end);
    },

    // Données complètes pour le calcul de pagination
    allFilteredData() {
      let filtered = [...this.allClients];
      
      // Filtre de recherche global
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

      // Filtre par lettre
      if (this.selectedLetter) {
        filtered = filtered.filter(c => 
          c.nom_francais && c.nom_francais.toUpperCase().startsWith(this.selectedLetter)
        );
      }

      // Filtre par ville
      if (this.selectedCity) {
        filtered = filtered.filter(c => 
          c.ville && c.ville === this.selectedCity
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
    // Computed property pour extraire les villes uniques des données clients
    uniqueCitiesFromData() {
      const cities = this.allClients
        .map(client => client.ville)
        .filter(ville => ville && ville.trim() !== '') // Filtrer les villes vides
        .filter((ville, index, array) => array.indexOf(ville) === index) // Supprimer les doublons
        .sort(); // Trier alphabétiquement
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

    // Nouvelles méthodes pour les filtres
    applyFilters() {
      this.currentPage = 1; // Retourner à la première page lors du filtrage
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

    onViewClient(record) {
      // Afficher les détails du client dans une modal ou rediriger vers une page de détails
      Swal.fire({
        title: `Détails du client : ${record.nom_francais}`,
        html: `
          <div class="client-details" style="text-align: left;">
            <div class="row mb-2">
              <div class="col-6"><strong>Nom (FR):</strong></div>
              <div class="col-6">${record.nom_francais || '-'}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Nom (AR):</strong></div>
              <div class="col-6">${record.nom_arabe || '-'}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>ICE:</strong></div>
              <div class="col-6">${record.ice || '-'}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Email:</strong></div>
              <div class="col-6">${record.email || '-'}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Téléphone:</strong></div>
              <div class="col-6">${record.telephone || '-'}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Ville:</strong></div>
              <div class="col-6">${record.ville || '-'}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Capital Social:</strong></div>
              <div class="col-6">${this.formatCurrency(record.capital_social)}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Type Client:</strong></div>
              <div class="col-6">${record.type_client || '-'}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6"><strong>Statut:</strong></div>
              <div class="col-6"><span class="badge ${record.status === 'ACTIVE' ? 'bg-success' : 'bg-danger'}">${record.status}</span></div>
            </div>
          </div>
        `,
        width: '600px',
        confirmButtonText: 'Fermer',
        confirmButtonClass: 'btn btn-primary',
        buttonsStyling: false,
        customClass: {
          popup: 'client-details-modal'
        }
      });
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
/* Styles existants conservés... */
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

/* Styles pour les filtres avec espaces */
.search-filters-row {
  margin-bottom: 20px;
}

.filter-item {
  display: flex;
  align-items: center;
}

.filter-select {
  width: 160px;
  height: 42px; /* Même hauteur que la barre de recherche */
  border: 1px solid #ced4da;
  border-radius: 6px;
  font-size: 0.875rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.filter-select:focus {
  border-color: #ced4da;
  box-shadow: 0 0 0 0.2rem rgba(206, 212, 218, 0.25);
}

/* 🟢 MODIFICATION: Bouton reset avec hover vert comme le bouton "Ajouter Client" */
.reset-btn {
  width: 42px;
  height: 42px; /* Même hauteur que la barre de recherche */
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s ease;
  padding: 0;
}

.reset-btn:hover {
  background-color: #ABC270 !important; /* Couleur personnalisée */
  border-color: #ABC270 !important;
  color: white !important;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(171, 194, 112, 0.3);
}

/* Table Responsive Wrapper */
.table-responsive-wrapper {
  width: 100%;
  overflow: hidden;
  border: 1px solid #f0f0f0;
  border-radius: 6px;
}

.table-scroll-container {
  overflow-x: auto;
  overflow-y: visible;
}

/* 🟢 MODIFICATION: Scrollbar verte */
.table-scroll-container::-webkit-scrollbar {
  height: 8px;
}

.table-scroll-container::-webkit-scrollbar-track {
  background: #f0f4e8;
  border-radius: 4px;
}

.table-scroll-container::-webkit-scrollbar-thumb {
  background: #ABC270;
  border-radius: 4px;
  transition: background 0.3s ease;
}

.table-scroll-container::-webkit-scrollbar-thumb:hover {
  background: #9bb05f;
}

.table-scroll-container {
  scrollbar-width: thin;
  scrollbar-color: #ABC270 #f0f4e8;
}

.full-width-table {
  width: 100%;
  min-width: 2500px;
}

.email-cell a,
.phone-cell a {
  text-decoration: none;
}

.email-cell a:hover,
.phone-cell a:hover {
  text-decoration: underline;
}

.address-cell {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

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

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 4px;
  text-decoration: none;
  transition: all 0.2s ease;
}

.edit-btn {
  background-color: #f0f9ff;
  color: #0369a1;
}

.edit-btn:hover {
  background-color: #e0f2fe;
  color: #0284c7;
}

.delete-btn {
  background-color: #fef2f2;
  color: #dc2626;
}

.delete-btn:hover {
  background-color: #fee2e2;
  color: #b91c1c;
}

/* Responsive design */
@media (max-width: 768px) {
  .search-filters-row {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }
  
  .filter-item {
    width: 100%;
  }
  
  .filter-select {
    width: 100%;
  }
  
  .reset-btn {
    width: 100%;
    justify-content: center;
  }

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

  .full-width-table {
    min-width: 1800px;
  }
}

:deep(.swal-actions-spaced) {
  gap: 2rem !important;
  justify-content: center !important;
  padding: 1rem !important;
}

:deep(.swal-actions-spaced .swal2-styled) {
  margin: 0.5rem 1rem !important;
  min-width: 120px !important;
}

:deep(.swal2-actions) {
  gap: 1.5rem !important;
  margin: 1.5rem 0 !important;
}

.view-btn {
  background-color: #f0f8ff;
  color: #1e40af;
}

.view-btn:hover {
  background-color: #dbeafe;
  color: #1d4ed8;
}

/* Styles pour la modal de détails */
:deep(.client-details-modal) {
  font-family: inherit;
}

:deep(.client-details-modal .swal2-html-container) {
  text-align: left !important;
}

:deep(.client-details .row) {
  margin-bottom: 8px;
  padding: 4px 0;
  border-bottom: 1px solid #f0f0f0;
}

:deep(.client-details .row:last-child) {
  border-bottom: none;
}
</style>
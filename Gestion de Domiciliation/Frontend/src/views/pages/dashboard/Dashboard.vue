<template>
  <layout-header></layout-header>
  <layout-sidebar></layout-sidebar>
  <div class="page-wrapper">
    <div class="content">
      <!-- Enhanced Header Section -->
      <div class="page-header modern-header" style="background-color: #ABC270;">
        <div class="header-content">
          <div class="page-title-section">
            <div class="title-wrapper">
              <div class="icon-wrapper">
                <i class="fas fa-chart-line text-primary"></i>
              </div>
              <div class="title-content">
                <h4 class="page-title">Dashboard</h4>
                <p class="page-subtitle">Vue d'ensemble de vos contrats et activités</p>
              </div>
            </div>
          </div>

          <div class="header-actions">
            <div class="export-actions">
              <button class="action-btn export-btn" @click="exportDashboardToPDF" data-bs-toggle="tooltip"
                title="Exporter le rapport en PDF">
                <i class="fas fa-file-pdf"></i>
              </button>
              <button class="action-btn export-btn" @click="exportDashboardToExcel" data-bs-toggle="tooltip"
                title="Exporter les données en Excel">
                <i class="fas fa-file-excel"></i>
              </button>
              <button class="action-btn refresh-btn" @click="refreshDashboard" data-bs-toggle="tooltip"
                title="Actualiser les données">
                <i class="fas fa-sync-alt" :class="{ 'fa-spin': isRefreshing }"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 col-12">
          <div class="card stat-card domiciliation-card">
            <div class="card-body">
              <div class="stat-content">
                <div class="stat-icon">
                  <i class="fas fa-building"></i>
                </div>
                <div class="stat-details">
                  <h3 class="stat-number">{{ stats.domiciliations.total }}</h3>
                  <p class="stat-label">Domiciliations</p>
                  <div class="stat-trend">
                    <span class="trend-indicator positive" v-if="stats.domiciliations.trend > 0">
                      <i class="fas fa-arrow-up"></i> +{{ stats.domiciliations.trend }}%
                    </span>
                    <span class="trend-indicator negative" v-else-if="stats.domiciliations.trend < 0">
                      <i class="fas fa-arrow-down"></i> {{ stats.domiciliations.trend }}%
                    </span>
                    <span class="trend-indicator neutral" v-else>
                      <i class="fas fa-minus"></i> 0%
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
          <div class="card stat-card bureau-card">
            <div class="card-body">
              <div class="stat-content">
                <div class="stat-icon">
                  <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                  <h3 class="stat-number">{{ stats.bureaux.total }}</h3>
                  <p class="stat-label">Bureaux Équipe</p>
                  <div class="stat-trend">
                    <span class="trend-indicator positive" v-if="stats.bureaux.trend > 0">
                      <i class="fas fa-arrow-up"></i> +{{ stats.bureaux.trend }}%
                    </span>
                    <span class="trend-indicator negative" v-else-if="stats.bureaux.trend < 0">
                      <i class="fas fa-arrow-down"></i> {{ stats.bureaux.trend }}%
                    </span>
                    <span class="trend-indicator neutral" v-else>
                      <i class="fas fa-minus"></i> 0%
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
          <div class="card stat-card revenue-card">
            <div class="card-body">
              <div class="stat-content">
                <div class="stat-icon">
                  <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-details">
                  <h3 class="stat-number">{{ formatMontant(stats.revenue.total) }}</h3>
                  <p class="stat-label">Chiffre d'Affaires</p>
                  <div class="stat-trend">
                    <span class="trend-indicator positive" v-if="stats.revenue.trend > 0">
                      <i class="fas fa-arrow-up"></i> +{{ stats.revenue.trend }}%
                    </span>
                    <span class="trend-indicator negative" v-else-if="stats.revenue.trend < 0">
                      <i class="fas fa-arrow-down"></i> {{ stats.revenue.trend }}%
                    </span>
                    <span class="trend-indicator neutral" v-else>
                      <i class="fas fa-minus"></i> 0%
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-sm-6 col-12">
          <div class="card stat-card clients-card">
            <div class="card-body">
              <div class="stat-content">
                <div class="stat-icon">
                  <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-details">
                  <h3 class="stat-number">{{ stats.clients.total }}</h3>
                  <p class="stat-label">Clients Actifs</p>
                  <div class="stat-trend">
                    <span class="trend-indicator positive" v-if="stats.clients.trend > 0">
                      <i class="fas fa-arrow-up"></i> +{{ stats.clients.trend }}%
                    </span>
                    <span class="trend-indicator negative" v-else-if="stats.clients.trend < 0">
                      <i class="fas fa-arrow-down"></i> {{ stats.clients.trend }}%
                    </span>
                    <span class="trend-indicator neutral" v-else>
                      <i class="fas fa-minus"></i> 0%
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="row mb-4">
        <div class="col-xl-8 col-12">
          <div class="card chart-card">
            <div class="card-header">
              <h5 class="card-title">
                <i class="fas fa-chart-area me-2"></i>
                Évolution des Contrats
              </h5>
              <div class="chart-controls">
                <select v-model="chartPeriod" @change="updateChartData" class="form-select form-select-sm">
                  <option value="7">7 derniers jours</option>
                  <option value="30">30 derniers jours</option>
                  <option value="365">1 an</option>
                </select>
              </div>
            </div>
            <div class="card-body">
              <canvas ref="contractsChart" height="300"></canvas>
            </div>
          </div>
        </div>

        <div class="col-xl-4 col-12">
          <div class="card chart-card">
            <div class="card-header">
              <h5 class="card-title">
                <i class="fas fa-chart-pie me-2"></i>
                Répartition par Statut
              </h5>
            </div>
            <div class="card-body">
              <canvas ref="statusChart" height="300"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Contracts Expiring Soon -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card alert-card">
            <div class="card-header">
              <h5 class="card-title">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Contrats Arrivant à Échéance
              </h5>
              <div class="alert-filters">
                <select v-model="expirationFilter" @change="filterExpiringContracts" class="form-select form-select-sm">
                  <option value="5">≤ 5 jours</option>
                  <option value="15">≤ 15 jours</option>
                  <option value="30">≤ 30 jours</option>
                </select>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Référence</th>
                      <th>Client</th>
                      <th>Type</th>
                      <th>Date Fin</th>
                      <th>Jours Restants</th>
                      <th>Montant</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="contract in expiringContracts" :key="contract.id"
                      :class="getExpirationRowClass(contract.days_remaining)">
                      <td>
                        <span class="reference-badge">{{ contract.reference_numero }}</span>
                      </td>
                      <td>{{ contract.client_nom }}</td>
                      <td>
                        <span :class="getContractTypeBadge(contract.type)">
                          {{ contract.type === 'domiciliation' ? 'Domiciliation' : 'Bureau Équipe' }}
                        </span>
                      </td>
                      <td>{{ formatDate(contract.date_fin) }}</td>
                      <td>
                        <span :class="getExpirationBadge(contract.days_remaining)">
                          {{ contract.days_remaining }} jour(s)
                        </span>
                      </td>
                      <td>{{ formatMontant(contract.montant) }}</td>
                      <td>
                        <div class="action-buttons">
                          <button class="btn btn-sm btn-primary" @click="renewContract(contract)">
                            <i class="fas fa-sync-alt"></i> Renouveler
                          </button>
                          <button class="btn btn-sm btn-info" @click="viewContract(contract)">
                            <i class="fas fa-eye"></i> Voir
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="row mb-4">
        <div class="col-xl-6 col-12">
          <div class="card activity-card">
            <div class="card-header">
              <h5 class="card-title">
                <i class="fas fa-history me-2"></i>
                Activités Récentes
              </h5>
            </div>
            <div class="card-body">
              <div class="activity-timeline">
                <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
                  <div class="activity-icon" :class="getActivityIconClass(activity.type)">
                    <i :class="getActivityIcon(activity.type)"></i>
                  </div>
                  <div class="activity-content">
                    <div class="activity-title">{{ activity.title }}</div>
                    <div class="activity-description">{{ activity.description }}</div>
                    <div class="activity-time">{{ formatDateTime(activity.created_at) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-6 col-12">
          <div class="card clients-card-detailed">
            <div class="card-header">
              <h5 class="card-title">
                <i class="fas fa-users me-2"></i>
                Évolution des Clients
              </h5>
            </div>
            <div class="card-body">
              <canvas ref="clientsChart" height="300"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card quick-actions-card">
            <div class="card-header">
              <h5 class="card-title">
                <i class="fas fa-bolt me-2"></i>
                Actions Rapides
              </h5>
            </div>
            <div class="card-body">
              <div class="quick-actions-grid">
                <div class="quick-action-item" @click="navigateTo('/domiciliations')">
                  <div class="quick-action-icon domiciliation">
                    <i class="fas fa-building"></i>
                  </div>
                  <div class="quick-action-content">
                    <h6>Nouvelle Domiciliation</h6>
                    <p>Créer un nouveau contrat de domiciliation</p>
                  </div>
                </div>

                <div class="quick-action-item" @click="navigateTo('admin/bureaux-equipe/BurreauEquipe')">
                  <div class="quick-action-icon bureau">
                    <i class="fas fa-users"></i>
                  </div>
                  <div class="quick-action-content">
                    <h6>Nouveau Bureau Équipe</h6>
                    <p>Créer un nouveau contrat de bureau équipe</p>
                  </div>
                </div>

                <div class="quick-action-item" @click="navigateTo('/admin/clients')">
                  <div class="quick-action-icon client">
                    <i class="fas fa-user-plus"></i>
                  </div>
                  <div class="quick-action-content">
                    <h6>Nouveau Client</h6>
                    <p>Ajouter un nouveau client</p>
                  </div>
                </div>

                <div class="quick-action-item" @click="generateReport()">
                  <div class="quick-action-icon report">
                    <i class="fas fa-file-alt"></i>
                  </div>
                  <div class="quick-action-content">
                    <h6>Générer Rapport</h6>
                    <p>Créer un rapport personnalisé</p>
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

<script>
import axios from "axios";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";
import Swal from "sweetalert2";
import Chart from 'chart.js/auto';

export default {
  name: 'Dashboard',
  data() {
    return {
      isRefreshing: false,
      chartPeriod: '30',
      expirationFilter: '15',

      // Statistics
      stats: {
        domiciliations: { total: 0, trend: 0 },
        bureaux: { total: 0, trend: 0 },
        revenue: { total: 0, trend: 0 },
        clients: { total: 0, trend: 0 }
      },

      // Charts
      contractsChart: null,
      statusChart: null,
      clientsChart: null,

      // Data
      expiringContracts: [],
      recentActivities: [],
      companyInfo: null,

      // Chart data - Initialisation avec des valeurs par défaut
      chartData: {
        contracts: {
          labels: [],
          domiciliations: [],
          bureaux: []
        },
        status: {
          labels: ["EN_COURS", "RESILIE", "REJETTE", "DEMANDE", "ACTIVE"],
          data: [12, 3, 4, 2, 9]
        },

        clients: {
          labels: [],
          data: []
        }
      }
    };
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

    // ==================== Data Fetching ====================
    async fetchDashboardData() {
      this.isRefreshing = true;
      try {
        await Promise.all([
          this.fetchStats(),
          this.fetchExpiringContracts(),
          this.fetchRecentActivities(),
          this.fetchChartData(),
          this.fetchCompanyInfo()
        ]);
      } catch (error) {
        console.error("Erreur lors du chargement du dashboard:", error);
        this.showErrorMessage("Erreur lors du chargement des données");
      } finally {
        this.isRefreshing = false;
      }
    },

    async fetchStats() {
      try {
        const response = await axios.get("/dashboard/stats", {
          headers: this.getApiHeaders(),
        });
        this.stats = response.data;
      } catch (error) {
        console.error("Erreur stats:", error);
        // Données de fallback
        this.stats = {
          domiciliations: { total: 45, trend: 12 },
          bureaux: { total: 23, trend: 8 },
          revenue: { total: 125000, trend: 15 },
          clients: { total: 68, trend: 5 }
        };
      }
    },

    async fetchExpiringContracts() {
      try {
        const response = await axios.get(`/dashboard/expiring-contracts?days=${this.expirationFilter}`, {
          headers: this.getApiHeaders(),
        });
        this.expiringContracts = response.data;
      } catch (error) {
        console.error("Erreur contrats expirants:", error);
        // Données de fallback
        this.expiringContracts = [
          {
            id: 1,
            reference_numero: "DOM-2024-001",
            client_nom: "Société ABC",
            type: "domiciliation",
            date_fin: "2024-01-15",
            days_remaining: 3,
            montant: 5000
          },
          {
            id: 2,
            reference_numero: "BUR-2024-002",
            client_nom: "Entreprise XYZ",
            type: "bureau",
            date_fin: "2024-01-20",
            days_remaining: 8,
            montant: 8000
          }
        ];
      }
    },

    async fetchRecentActivities() {
      try {
        const response = await axios.get("/dashboard/recent-activities", {
          headers: this.getApiHeaders(),
        });
        this.recentActivities = response.data;
      } catch (error) {
        console.error("Erreur activités récentes:", error);
        // Données de fallback
        this.recentActivities = [
          {
            id: 1,
            type: "creation",
            title: "Nouveau contrat créé",
            description: "Contrat DOM-2024-003 pour Société DEF",
            created_at: new Date().toISOString()
          },
          {
            id: 2,
            type: "renewal",
            title: "Contrat renouvelé",
            description: "Renouvellement de BUR-2023-015",
            created_at: new Date(Date.now() - 3600000).toISOString()
          },
          {
            id: 3,
            type: "payment",
            title: "Paiement reçu",
            description: "Paiement de 5000 MAD pour DOM-2024-001",
            created_at: new Date(Date.now() - 7200000).toISOString()
          }
        ];
      }
    },

    async fetchChartData() {
      try {
        const response = await axios.get(`/dashboard/chart-data?period=${this.chartPeriod}`, {
          headers: this.getApiHeaders(),
        });

        // Vérifier que la réponse contient les données attendues
        if (response.data) {
          this.chartData = {
            contracts: response.data.contracts || this.chartData.contracts,
            status: response.data.status || this.chartData.status,
            clients: response.data.clients || this.chartData.clients
          };
        }

        // Mettre à jour les graphiques seulement si les données sont valides
        this.updateCharts();
      } catch (error) {
        console.error("Erreur données graphiques:", error);
        this.generateFallbackChartData();
        this.updateCharts();
      }
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
            logo: response.data.logo || response.data.logo_path,
            rc: response.data.rc,
            identifiant_fiscal: response.data.identifiant_fiscal,
            tp: response.data.tp,
            ice: response.data.ice
          };
        }
      } catch (err) {
        console.error("Erreur informations société:", err);
        this.companyInfo = null;
      }
    },

    generateFallbackChartData() {
      const days = parseInt(this.chartPeriod);
      const labels = [];
      const domiciliations = [];
      const bureaux = [];
      const clients = [];

      for (let i = days - 1; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);

        if (days <= 30) {
          labels.push(date.toLocaleDateString('fr-FR', { month: 'short', day: 'numeric' }));
        } else {
          labels.push(date.toLocaleDateString('fr-FR', { month: 'short', year: '2-digit' }));
        }

        domiciliations.push(Math.floor(Math.random() * 10) + 1);
        bureaux.push(Math.floor(Math.random() * 8) + 1);
        clients.push(Math.floor(Math.random() * 15) + 5);
      }

      this.chartData = {
        contracts: { labels, domiciliations, bureaux },
        status: {
          labels: ["EN_COURS", "RESILIE", "REJETTE", "DEMANDE", "ACTIVE"],
          data: [12, 3, 4, 2, 9]
        },
        clients: { labels, data: clients }
      };
    },

    // ==================== Chart Management ====================
    initializeCharts() {
      this.$nextTick(() => {
        // S'assurer que les données sont initialisées avant de créer les graphiques
        if (!this.chartData.clients || !this.chartData.clients.labels) {
          this.generateFallbackChartData();
        }

        this.createContractsChart();
        this.createStatusChart();
        this.createClientsChart();
      });
    },

    createContractsChart() {
      const ctx = this.$refs.contractsChart?.getContext('2d');
      if (!ctx) return;

      if (this.contractsChart) {
        this.contractsChart.destroy();
      }

      // Vérifier que les données existent
      const contractsData = this.chartData.contracts || { labels: [], domiciliations: [], bureaux: [] };

      this.contractsChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: contractsData.labels || [],
          datasets: [
            {
              label: 'Domiciliations',
              data: contractsData.domiciliations || [],
              backgroundColor: 'rgba(171, 194, 112, 0.8)',
              borderColor: '#ABC270',
              borderWidth: 1
            },
            {
              label: 'Bureaux Équipe',
              data: contractsData.bureaux || [],
              backgroundColor: 'rgba(102, 126, 234, 0.8)',
              borderColor: '#667eea',
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    },

    createStatusChart() {
      const ctx = this.$refs.statusChart?.getContext('2d');
      if (!ctx) return;

      if (this.statusChart) {
        this.statusChart.destroy();
      }

      const statusData = this.chartData.status || { labels: [], data: [] };

      // Mapping statuts → couleurs
      const colorMap = {
        DEMANDE: '#ffc107',     // jaune
        EN_COURS: '#ABC270',    // vert
        REJETTE: '#dc3545',     // rouge
        RESILIE: '#ff9800',     // orange
        ACTIVE: '#4caf50',      // vert foncé
        EXPIRE: '#6c757d'       // gris (si jamais tu l’utilises aussi)
      };

      // Appliquer les couleurs en fonction des labels renvoyés par l'API
      const colors = statusData.labels.map(label => colorMap[label] || '#999');

      this.statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: statusData.labels,
          datasets: [{
            data: statusData.data,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#fff'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
            }
          }
        }
      });
    },


    createClientsChart() {
      const ctx = this.$refs.clientsChart?.getContext('2d');
      if (!ctx) return;

      if (this.clientsChart) {
        this.clientsChart.destroy();
      }

      // Vérifier que les données existent et initialiser si nécessaire
      const clientsData = this.chartData.clients || { labels: [], data: [] };

      this.clientsChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: clientsData.labels || [],
          datasets: [{
            label: 'Nouveaux Clients',
            data: clientsData.data || [],
            borderColor: '#4facfe',
            backgroundColor: 'rgba(79, 172, 254, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#4facfe',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                stepSize: 1
              }
            }
          }
        }
      });
    },

    updateCharts() {
      // Vérifier et mettre à jour le graphique des contrats
      if (this.contractsChart && this.chartData.contracts) {
        this.contractsChart.data.labels = this.chartData.contracts.labels || [];
        this.contractsChart.data.datasets[0].data = this.chartData.contracts.domiciliations || [];
        this.contractsChart.data.datasets[1].data = this.chartData.contracts.bureaux || [];
        this.contractsChart.update();
      }

      // Vérifier et mettre à jour le graphique des statuts
      if (this.statusChart && this.chartData.status) {
        this.statusChart.data.datasets[0].data = this.chartData.status.data || [0, 0, 0];
        this.statusChart.update();
      }

      // Vérifier et mettre à jour le graphique des clients
      if (this.clientsChart && this.chartData.clients) {
        this.clientsChart.data.labels = this.chartData.clients.labels || [];
        this.clientsChart.data.datasets[0].data = this.chartData.clients.data || [];
        this.clientsChart.update();
      }
    },

    async updateChartData() {
      await this.fetchChartData();
    },

    // ==================== Event Handlers ====================
    async refreshDashboard() {
      await this.fetchDashboardData();
      this.showSuccessMessage("Données actualisées avec succès");
    },

    async filterExpiringContracts() {
      await this.fetchExpiringContracts();
    },

    renewContract(contract) {
      const route = contract.type === 'domiciliation' ? '/domiciliations' : '/admin/bureaux-equipe/BurreauEquipe';
      this.$router.push(route);
    },

    viewContract(contract) {
      const route = contract.type === 'domiciliation' ? '/domiciliations' : '/admin/bureaux-equipe/BurreauEquipe';
      this.$router.push(route);
    },

    navigateTo(route) {
      this.$router.push(route);
    },

    generateReport() {
      this.exportDashboardToPDF();
    },

    // ==================== Utility Methods ====================
    formatDate(dateString) {
      if (!dateString) return "-";
      const date = new Date(dateString);
      return date.toLocaleDateString("fr-FR");
    },

    formatDateTime(dateString) {
      if (!dateString) return "-";
      const date = new Date(dateString);
      return date.toLocaleString("fr-FR");
    },

    formatMontant(montant) {
      return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "MAD",
      }).format(montant);
    },

    getExpirationRowClass(days) {
      if (days <= 0) return 'table-danger';
      if (days <= 5) return 'table-warning';
      if (days <= 15) return 'table-info';
      return '';
    },

    getExpirationBadge(days) {
      if (days <= 0) return 'badge bg-danger';
      if (days <= 5) return 'badge bg-warning text-dark';
      if (days <= 15) return 'badge bg-info';
      return 'badge bg-success';
    },

    getContractTypeBadge(type) {
      return type === 'domiciliation' ? 'badge bg-primary' : 'badge bg-secondary';
    },

    getActivityIconClass(type) {
      const classes = {
        creation: 'activity-icon-success',
        renewal: 'activity-icon-info',
        payment: 'activity-icon-warning',
        termination: 'activity-icon-danger'
      };
      return classes[type] || 'activity-icon-secondary';
    },

    getActivityIcon(type) {
      const icons = {
        creation: 'fas fa-plus',
        renewal: 'fas fa-sync-alt',
        payment: 'fas fa-money-bill',
        termination: 'fas fa-times'
      };
      return icons[type] || 'fas fa-info';
    },

    // ==================== Export Methods ====================
    exportDashboardToPDF() {
      const printWindow = window.open('', '_blank');
      const pdfContent = this.generateDashboardHTML();

      printWindow.document.open();
      printWindow.document.write(pdfContent);
      printWindow.document.close();
      printWindow.document.title = "Rapport Dashboard";
      printWindow.focus();
    },

    generateDashboardHTML() {
      const currentDate = new Date();
      const formattedDate = currentDate.toLocaleDateString('fr-FR');
      const formattedTime = currentDate.toLocaleTimeString('fr-FR');

      return `<!DOCTYPE html>
<html>
  <head>
    <title>Rapport Dashboard</title>
    <meta charset="UTF-8">
    <style>
      body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
      .header { display: flex; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #ddd; padding-bottom: 15px; }
      .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
      .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
      .stat-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center; }
      .stat-number { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
      .stat-label { color: #666; }
      table { width: 100%; border-collapse: collapse; margin-top: 20px; }
      th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
      th { background-color: #f5f5f5; }
      .no-print { margin-top: 30px; text-align: center; }
      .print-btn { background-color: #007bff; color: white; border: none; padding: 10px 20px; margin: 0 5px; border-radius: 4px; cursor: pointer; }
      @media print { .no-print { display: none; } }
    </style>
  </head>
  <body>
    <div class="header">
      <div>
        <div class="title">Rapport Dashboard</div>
        <div>Généré le ${formattedDate} à ${formattedTime}</div>
      </div>
    </div>
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number">${this.stats.domiciliations.total}</div>
        <div class="stat-label">Domiciliations</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">${this.stats.bureaux.total}</div>
        <div class="stat-label">Bureaux Équipe</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">${this.formatMontant(this.stats.revenue.total)}</div>
        <div class="stat-label">Chiffre d'Affaires</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">${this.stats.clients.total}</div>
        <div class="stat-label">Clients Actifs</div>
      </div>
    </div>

    <h3>Contrats Arrivant à Échéance</h3>
    <table>
      <thead>
        <tr>
          <th>Référence</th>
          <th>Client</th>
          <th>Type</th>
          <th>Date Fin</th>
          <th>Jours Restants</th>
          <th>Montant</th>
        </tr>
      </thead>
      <tbody>
        ${this.expiringContracts.map(contract => `
          <tr>
            <td>${contract.reference_numero}</td>
            <td>${contract.client_nom}</td>
            <td>${contract.type === 'domiciliation' ? 'Domiciliation' : 'Bureau Équipe'}</td>
            <td>${this.formatDate(contract.date_fin)}</td>
            <td>${contract.days_remaining} jour(s)</td>
            <td>${this.formatMontant(contract.montant)}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
    
    <div class="no-print">
      <button onclick="window.print()" class="print-btn">Imprimer</button>
      <button onclick="window.close()" class="print-btn">Fermer</button>
    </div>
  </body>
</html>`;
    },

    exportDashboardToExcel() {
      const dashboardData = [
        ['Statistiques Dashboard', '', '', ''],
        ['Type', 'Total', 'Tendance (%)', ''],
        ['Domiciliations', this.stats.domiciliations.total, this.stats.domiciliations.trend, ''],
        ['Bureaux Équipe', this.stats.bureaux.total, this.stats.bureaux.trend, ''],
        ['Chiffre d\'Affaires', this.stats.revenue.total, this.stats.revenue.trend, ''],
        ['Clients Actifs', this.stats.clients.total, this.stats.clients.trend, ''],
        ['', '', '', ''],
        ['Contrats Arrivant à Échéance', '', '', ''],
        ['Référence', 'Client', 'Type', 'Jours Restants'],
        ...this.expiringContracts.map(contract => [
          contract.reference_numero,
          contract.client_nom,
          contract.type === 'domiciliation' ? 'Domiciliation' : 'Bureau Équipe',
          contract.days_remaining
        ])
      ];

      const worksheet = XLSX.utils.aoa_to_sheet(dashboardData);
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "Dashboard");

      const excelBuffer = XLSX.write(workbook, {
        bookType: "xlsx",
        type: "array",
      });
      const data = new Blob([excelBuffer], {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });
      saveAs(data, `dashboard_${new Date().toISOString().split("T")[0]}.xlsx`);
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
    // Initialiser les données de fallback avant de charger les données réelles
    this.generateFallbackChartData();

    await this.fetchDashboardData();
    this.initializeCharts();
  },

  beforeUnmount() {
    if (this.contractsChart) this.contractsChart.destroy();
    if (this.statusChart) this.statusChart.destroy();
    if (this.clientsChart) this.clientsChart.destroy();
  }
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
}

.export-btn,
.refresh-btn {
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

.export-btn:hover,
.refresh-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}

/* ==================== Statistics Cards ==================== */
.stat-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.domiciliation-card .stat-icon {
  background: linear-gradient(135deg, #ABC270, #9bb05f);
}

.bureau-card .stat-icon {
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.revenue-card .stat-icon {
  background: linear-gradient(135deg, #f093fb, #f5576c);
}

.clients-card .stat-icon {
  background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.stat-details {
  flex: 1;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
  color: #2c3e50;
}

.stat-label {
  color: #6c757d;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.trend-indicator {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.trend-indicator.positive {
  background: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

.trend-indicator.negative {
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

.trend-indicator.neutral {
  background: rgba(108, 117, 125, 0.1);
  color: #6c757d;
}

/* ==================== Chart Cards ==================== */
.chart-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  margin-bottom: 1.5rem;
}

.chart-card .card-header {
  background: linear-gradient(135deg, #f8f9fa, #e9ecef);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 16px 16px 0 0;
}

.chart-controls select {
  min-width: 150px;
}

/* ==================== Alert Card ==================== */
.alert-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border-left: 4px solid #ffc107;
}

.alert-card .card-header {
  background: linear-gradient(135deg, #fff3cd, #ffeaa7);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 16px 16px 0 0;
}

.alert-filters select {
  min-width: 120px;
}

.reference-badge {
  background: #ABC270;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

/* ==================== Activity Card ==================== */
.activity-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.activity-timeline {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid #f1f3f4;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.activity-icon-success {
  background: #28a745;
}

.activity-icon-info {
  background: #17a2b8;
}

.activity-icon-warning {
  background: #ffc107;
}

.activity-icon-danger {
  background: #dc3545;
}

.activity-icon-secondary {
  background: #6c757d;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 0.25rem;
}

.activity-description {
  color: #6c757d;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.activity-time {
  color: #adb5bd;
  font-size: 0.75rem;
}

/* ==================== Clients Card ==================== */
.clients-card-detailed {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

/* ==================== Quick Actions ==================== */
.quick-actions-card {
  border: none;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.quick-action-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  border: 2px solid #f1f3f4;
  border-radius: 12px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.quick-action-item:hover {
  border-color: #ABC270;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(171, 194, 112, 0.2);
}

.quick-action-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.quick-action-icon.domiciliation {
  background: linear-gradient(135deg, #ABC270, #9bb05f);
}

.quick-action-icon.bureau {
  background: linear-gradient(135deg, #667eea, #764ba2);
}

.quick-action-icon.client {
  background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.quick-action-icon.report {
  background: linear-gradient(135deg, #f093fb, #f5576c);
}

.quick-action-content h6 {
  margin: 0 0 0.25rem 0;
  font-weight: 600;
  color: #2c3e50;
}

.quick-action-content p {
  margin: 0;
  color: #6c757d;
  font-size: 0.875rem;
}

/* ==================== Responsive Design ==================== */
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
  }

  .quick-actions-grid {
    grid-template-columns: 1fr;
  }

  .stat-content {
    flex-direction: column;
    text-align: center;
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

  .stat-number {
    font-size: 1.5rem;
  }

  .quick-action-item {
    flex-direction: column;
    text-align: center;
  }
}
</style>
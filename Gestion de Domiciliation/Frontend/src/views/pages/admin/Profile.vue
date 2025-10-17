<template>
    <layout-header></layout-header>
    <layout-sidebar></layout-sidebar>
    <div class="page-wrapper">
        <div class="content">
            <!-- Enhanced Header Section -->
            <div class="page-header modern-header" style="background-color: #ABC270; ">
                <div class="header-content">
                    <div class="page-title-section">
                        <div class="title-wrapper">
                            <div class="icon-wrapper">
                                <i class="fas fa-user-circle text-white"></i>
                            </div>
                            <div class="title-content">
                                <h4 class="page-title">Mon Profil</h4>
                                <p class="page-subtitle">Gérez vos informations personnelles et paramètres de sécurité
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="header-actions">
                        <div class="user-info-badge">
                            <div class="avatar-wrapper">
                                <img :src="avatarUrl" :alt="user.name" class="user-avatar" />
                                <div class="status-indicator" :class="user.status === 'ACTIVE' ? 'online' : 'offline'">
                                </div>
                            </div>
                            <div class="user-details">
                                <span class="user-name">{{ user.name }}</span>
                                <span class="user-role">{{ user.is_admin ? 'Administrateur' : 'Utilisateur' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-container">
                <div class="row g-4">
                    <!-- Left Column - Profile Info -->
                    <div class="col-lg-4">
                        <!-- Profile Card -->
                        <div class="card modern-card profile-card">
                            <div class="card-body modern-card-body text-center">
                                <div class="profile-avatar-section">
                                    <div class="avatar-container">
                                        <img :src="avatarUrl" :alt="user.name" class="profile-avatar" />
                                        <button class="avatar-edit-btn" @click="triggerAvatarUpload"
                                            :disabled="!isEditingProfile">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                        <input ref="avatarInput" type="file" accept="image/*"
                                            @change="handleAvatarUpload" class="d-none" />
                                    </div>
                                </div>

                                <h5 class="profile-name">{{ user.name }}</h5>
                                <p class="profile-email">{{ user.email }}</p>

                                <div class="profile-stats">
                                    <div class="stat-item">
                                        <div class="stat-value">{{ formatDate(user.created_at) }}</div>
                                        <div class="stat-label">Membre depuis</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value">
                                            <span :class="user.email_verified_at ? 'text-success' : 'text-warning'">
                                                {{ user.email_verified_at ? 'Vérifié' : 'Non vérifié' }}
                                            </span>
                                        </div>
                                        <div class="stat-label">Statut Email</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value">
                                            <span :class="user.status === 'ACTIVE' ? 'text-success' : 'text-danger'">
                                                {{ user.status === 'ACTIVE' ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </div>
                                        <div class="stat-label">Statut Compte</div>
                                    </div>
                                </div>

                                <!-- Email Verification -->
                                <div v-if="!user.email_verified_at" class="verification-section">
                                    <div class="alert alert-warning modern-alert">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Votre email n'est pas vérifié
                                    </div>
                                    <button class="btn btn-outline-primary modern-btn" @click="sendVerificationEmail"
                                        :disabled="verificationLoading">
                                        <i class="fas fa-envelope me-1"></i>
                                        {{ verificationLoading ? 'Envoi...' : 'Renvoyer la vérification' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card modern-card mt-4">
                            <div class="card-header modern-card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-bolt me-2 text-warning"></i>
                                    Actions Rapides
                                </h6>
                            </div>
                            <div class="card-body modern-card-body">
                                <div class="quick-actions">
                                    
                                    <button class="action-btn text-danger" @click="confirmLogout">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Se déconnecter</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Forms -->
                    <div class="col-lg-8">
                        <!-- Profile Information Form -->
                        <div class="card modern-card mb-4">
                            <div class="card-header modern-card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        Informations Personnelles
                                    </h6>
                                    <button class="btn modern-btn"
                                        :class="isEditingProfile ? 'btn-success' : 'btn-outline-primary'"
                                        @click="toggleProfileEdit" :disabled="profileLoading">
                                        <i :class="isEditingProfile ? 'fas fa-save' : 'fas fa-edit'" class="me-1"></i>
                                        {{ profileLoading ? 'Sauvegarde...' : (isEditingProfile ? 'Sauvegarder' :
                                        'Modifier') }}
                                    </button>
                                </div>
                            </div>
                            <div class="card-body modern-card-body">
                                <form @submit.prevent="updateProfile">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label required">
                                                    <i class="fas fa-user me-1"></i>
                                                    Nom complet
                                                </label>
                                                <input type="text" class="form-control modern-input" :class="{
                                                    'form-control-readonly': !isEditingProfile,
                                                    'is-invalid': profileErrors.name,
                                                    'is-valid': !profileErrors.name && profileForm.name && isEditingProfile
                                                }" v-model="profileForm.name" :readonly="!isEditingProfile"
                                                    placeholder="Votre nom complet" />
                                                <div v-if="profileErrors.name" class="invalid-feedback">
                                                    {{ Array.isArray(profileErrors.name) ? profileErrors.name[0] :
                                                    profileErrors.name }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label required">
                                                    <i class="fas fa-envelope me-1"></i>
                                                    Adresse email
                                                </label>
                                                <input type="email" class="form-control modern-input" :class="{
                                                    'form-control-readonly': !isEditingProfile,
                                                    'is-invalid': profileErrors.email,
                                                    'is-valid': !profileErrors.email && profileForm.email && isEditingProfile
                                                }" v-model="profileForm.email" :readonly="!isEditingProfile" placeholder="votre@email.com" />
                                                <div v-if="profileErrors.email" class="invalid-feedback">
                                                    {{ Array.isArray(profileErrors.email) ? profileErrors.email[0] :
                                                    profileErrors.email }}
                                                </div>
                                                <small v-if="isEditingProfile && profileForm.email !== user.email"
                                                    class="form-text text-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Changer l'email nécessitera une nouvelle vérification
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Password Change Form -->
                        <div class="card modern-card mb-4">
                            <div class="card-header modern-card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-lock me-2 text-danger"></i>
                                        Sécurité du Compte
                                    </h6>
                                    <div class="btn-group" role="group">
                                        <button 
                                            type="button" 
                                            class="btn modern-btn"
                                            :class="passwordMode === 'change' ? 'btn-primary' : 'btn-outline-primary'"
                                            @click="setPasswordMode('change')"
                                        >
                                            <i class="fas fa-key me-1"></i>
                                            Changer
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn modern-btn"
                                            :class="passwordMode === 'forgot' ? 'btn-warning' : 'btn-outline-warning'"
                                            @click="setPasswordMode('forgot')"
                                        >
                                            <i class="fas fa-question-circle me-1"></i>
                                            Oublié
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body modern-card-body">
                                <!-- Mode: Change Password -->
                                <div v-if="passwordMode === 'change'">
                                   

                                    <form @submit.prevent="updatePassword">
                                        <div class="row g-4">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="form-label required">
                                                        <i class="fas fa-key me-1"></i>
                                                        Mot de passe actuel
                                                    </label>
                                                    <div class="password-input-group">
                                                        <input :type="showCurrentPassword ? 'text' : 'password'"
                                                            class="form-control modern-input" :class="{
                                                                'is-invalid': passwordErrors.current_password,
                                                                'is-valid': !passwordErrors.current_password && passwordForm.current_password
                                                            }" v-model="passwordForm.current_password" placeholder="Votre mot de passe actuel" />
                                                        <button type="button" class="password-toggle-btn"
                                                            @click="showCurrentPassword = !showCurrentPassword">
                                                            <i
                                                                :class="showCurrentPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                                        </button>
                                                    </div>
                                                    <div v-if="passwordErrors.current_password" class="invalid-feedback">
                                                        {{ Array.isArray(passwordErrors.current_password) ?
                                                            passwordErrors.current_password[0] : passwordErrors.current_password
                                                        }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label required">
                                                        <i class="fas fa-lock me-1"></i>
                                                        Nouveau mot de passe
                                                    </label>
                                                    <div class="password-input-group">
                                                        <input :type="showNewPassword ? 'text' : 'password'"
                                                            class="form-control modern-input" :class="{
                                                                'is-invalid': passwordErrors.password,
                                                                'is-valid': !passwordErrors.password && passwordForm.password && passwordStrength.score >= 3
                                                            }" v-model="passwordForm.password" placeholder="Nouveau mot de passe"
                                                            @input="checkPasswordStrength" />
                                                        <button type="button" class="password-toggle-btn"
                                                            @click="showNewPassword = !showNewPassword">
                                                            <i
                                                                :class="showNewPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                                        </button>
                                                    </div>
                                                    <div v-if="passwordErrors.password" class="invalid-feedback">
                                                        {{ Array.isArray(passwordErrors.password) ?
                                                        passwordErrors.password[0] : passwordErrors.password }}
                                                    </div>

                                                    <!-- Password Strength Indicator -->
                                                    <div v-if="passwordForm.password" class="password-strength mt-2">
                                                        <div class="strength-bar">
                                                            <div class="strength-fill" :class="passwordStrength.class"
                                                                :style="{ width: passwordStrength.percentage + '%' }"></div>
                                                        </div>
                                                        <small class="strength-text" :class="passwordStrength.class">
                                                            {{ passwordStrength.text }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label required">
                                                        <i class="fas fa-check-double me-1"></i>
                                                        Confirmer le mot de passe
                                                    </label>
                                                    <div class="password-input-group">
                                                        <input :type="showConfirmPassword ? 'text' : 'password'"
                                                            class="form-control modern-input" :class="{
                                                                'is-invalid': passwordErrors.password_confirmation,
                                                                'is-valid': !passwordErrors.password_confirmation && passwordForm.password_confirmation && passwordForm.password === passwordForm.password_confirmation
                                                            }" v-model="passwordForm.password_confirmation"
                                                            placeholder="Confirmer le nouveau mot de passe" />
                                                        <button type="button" class="password-toggle-btn"
                                                            @click="showConfirmPassword = !showConfirmPassword">
                                                            <i
                                                                :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                                        </button>
                                                    </div>
                                                    <div v-if="passwordErrors.password_confirmation"
                                                        class="invalid-feedback">
                                                        {{ Array.isArray(passwordErrors.password_confirmation) ?
                                                            passwordErrors.password_confirmation[0] :
                                                        passwordErrors.password_confirmation }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-actions">
                                                    <button type="submit" class="btn btn-danger modern-btn"
                                                        :disabled="passwordLoading || !isPasswordFormValid">
                                                        <i class="fas fa-shield-alt me-1"></i>
                                                        {{ passwordLoading ? 'Mise à jour...' : 'Changer le mot de passe' }}
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary modern-btn ms-2"
                                                        @click="resetPasswordForm">
                                                        <i class="fas fa-times me-1"></i>
                                                        Annuler
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Mode: Forgot Password -->
                                <div v-else-if="passwordMode === 'forgot'">
                                   

                                    <form @submit.prevent="sendPasswordResetLink">
                                        <div class="row g-4">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="form-label required">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        Adresse email
                                                    </label>
                                                    <input 
                                                        type="email" 
                                                        class="form-control modern-input"
                                                        :class="{
                                                            'is-invalid': forgotPasswordErrors.email,
                                                            'is-valid': !forgotPasswordErrors.email && forgotPasswordForm.email
                                                        }"
                                                        v-model="forgotPasswordForm.email" 
                                                        placeholder="Votre adresse email"
                                                        :readonly="forgotPasswordLoading"
                                                    />
                                                    <div v-if="forgotPasswordErrors.email" class="invalid-feedback">
                                                        {{ Array.isArray(forgotPasswordErrors.email) ? forgotPasswordErrors.email[0] : forgotPasswordErrors.email }}
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Nous enverrons un lien de réinitialisation à cette adresse
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="form-actions">
                                                    <button 
                                                        type="submit" 
                                                        class="btn btn-warning modern-btn"
                                                        :disabled="forgotPasswordLoading || !forgotPasswordForm.email"
                                                    >
                                                        <i class="fas fa-paper-plane me-1"></i>
                                                        {{ forgotPasswordLoading ? 'Envoi en cours...' : 'Envoyer le lien de réinitialisation' }}
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        class="btn btn-outline-secondary modern-btn ms-2"
                                                        @click="resetForgotPasswordForm"
                                                        :disabled="forgotPasswordLoading"
                                                    >
                                                        <i class="fas fa-times me-1"></i>
                                                        Annuler
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Success Message -->
                                            <div v-if="forgotPasswordSuccess" class="col-12">
                                                <div class="alert alert-success modern-alert">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    <strong>Email envoyé!</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Log -->
                        <div class="card modern-card">
                            <div class="card-header modern-card-header">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-history me-2 text-info"></i>
                                    Informations du Compte
                                </h6>
                            </div>
                            <div class="card-body modern-card-body">
                                <div class="account-info">
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-calendar-plus text-success"></i>
                                            </div>
                                            <div class="info-content">
                                                <div class="info-title">Compte créé</div>
                                                <div class="info-value">{{ formatDateTime(user.created_at) }}</div>
                                            </div>
                                        </div>

                                        
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-shield-alt text-warning"></i>
                                            </div>
                                            <div class="info-content">
                                                <div class="info-title">Type de compte</div>
                                                <div class="info-value">{{ user.is_admin ? 'Administrateur' :
                                                    'Utilisateur' }}</div>
                                            </div>
                                        </div>

                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-power-off"
                                                    :class="user.status === 'ACTIVE' ? 'text-success' : 'text-danger'"></i>
                                            </div>
                                            <div class="info-content">
                                                <div class="info-title">Statut</div>
                                                <div class="info-value">{{ user.status === 'ACTIVE' ? 'Actif' :
                                                    'Inactif' }}</div>
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
    </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'ProfilePage',
    setup() {
        // User data
        const user = ref({
            id: null,
            name: '',
            email: '',
            email_verified_at: null,
            is_admin: false,
            status: 'ACTIVE',
            created_at: null,
            updated_at: null,
        });

        // Form states
        const isEditingProfile = ref(false);
        const passwordMode = ref('change'); // 'change' or 'forgot'
        
        const profileForm = ref({
            name: '',
            email: '',
        });

        const passwordForm = ref({
            current_password: '',
            password: '',
            password_confirmation: '',
        });

        const forgotPasswordForm = ref({
            email: '',
        });

        // Error states
        const profileErrors = ref({});
        const passwordErrors = ref({});
        const forgotPasswordErrors = ref({});

        // Loading states
        const profileLoading = ref(false);
        const passwordLoading = ref(false);
        const forgotPasswordLoading = ref(false);
        const verificationLoading = ref(false);

        // Success states
        const forgotPasswordSuccess = ref(false);

        // Password visibility
        const showCurrentPassword = ref(false);
        const showNewPassword = ref(false);
        const showConfirmPassword = ref(false);

        // Password strength
        const passwordStrength = ref({
            score: 0,
            percentage: 0,
            text: '',
            class: '',
        });

        // Avatar input ref
        const avatarInput = ref(null);

        // Computed properties
        const avatarUrl = computed(() => {
            return `https://ui-avatars.com/api/?name=${encodeURIComponent(user.value.name || 'User')}&background=ABC270&color=fff&size=200`;
        });

        const isPasswordFormValid = computed(() => {
            return passwordForm.value.current_password &&
                passwordForm.value.password &&
                passwordForm.value.password_confirmation &&
                passwordForm.value.password === passwordForm.value.password_confirmation &&
                passwordStrength.value.score >= 3;
        });

        // Methods
        const fetchUserData = async () => {
            try {
                const response = await axios.get('/profile');
                user.value = response.data;

                // Initialize profile form
                profileForm.value = {
                    name: user.value.name || '',
                    email: user.value.email || '',
                };

                // Initialize forgot password form with user email
                forgotPasswordForm.value = {
                    email: user.value.email || '',
                };
            } catch (error) {
                console.error('Error fetching user data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les données utilisateur',
                });
            }
        };

        const setPasswordMode = (mode) => {
            passwordMode.value = mode;
            // Reset forms when switching modes
            resetPasswordForm();
            resetForgotPasswordForm();
        };

        const toggleProfileEdit = async () => {
            if (isEditingProfile.value) {
                await updateProfile();
            } else {
                isEditingProfile.value = true;
            }
        };

        const updateProfile = async () => {
            profileLoading.value = true;
            profileErrors.value = {};

            try {
                const response = await axios.put('/profile', profileForm.value);
                user.value = response.data;
                isEditingProfile.value = false;

                Swal.fire({
                    icon: 'success',
                    title: 'Succès!',
                    text: 'Profil mis à jour avec succès',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            } catch (error) {
                if (error.response?.data?.messages) {
                    profileErrors.value = error.response.data.messages;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.response?.data?.error || 'Erreur lors de la mise à jour du profil',
                });
            } finally {
                profileLoading.value = false;
            }
        };

        const updatePassword = async () => {
            passwordLoading.value = true;
            passwordErrors.value = {};

            try {
                await axios.put('/password', passwordForm.value);
                resetPasswordForm();

                Swal.fire({
                    icon: 'success',
                    title: 'Succès!',
                    text: 'Mot de passe mis à jour avec succès',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            } catch (error) {
                if (error.response?.data?.errors) {
                    passwordErrors.value = error.response.data.errors;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.response?.data?.message || 'Erreur lors de la mise à jour du mot de passe',
                });
            } finally {
                passwordLoading.value = false;
            }
        };

        const sendPasswordResetLink = async () => {
            forgotPasswordLoading.value = true;
            forgotPasswordErrors.value = {};
            forgotPasswordSuccess.value = false;

            try {
                await axios.post('/forgot-password', forgotPasswordForm.value);
                
                forgotPasswordSuccess.value = true;
                
                Swal.fire({
                    icon: 'success',
                    title: 'Email envoyé!',
                    text: 'Si cette adresse existe, vous recevrez un lien de réinitialisation',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            } catch (error) {
                if (error.response?.data?.errors) {
                    forgotPasswordErrors.value = error.response.data.errors;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: error.response?.data?.message || 'Erreur lors de l\'envoi du lien de réinitialisation',
                });
            } finally {
                forgotPasswordLoading.value = false;
            }
        };

        const resetPasswordForm = () => {
            passwordForm.value = {
                current_password: '',
                password: '',
                password_confirmation: '',
            };
            passwordErrors.value = {};
            passwordStrength.value = { score: 0, percentage: 0, text: '', class: '' };
        };

        const resetForgotPasswordForm = () => {
            forgotPasswordForm.value = {
                email: user.value.email || '',
            };
            forgotPasswordErrors.value = {};
            forgotPasswordSuccess.value = false;
        };

        const checkPasswordStrength = () => {
            const password = passwordForm.value.password;
            let score = 0;

            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/\d/.test(password)) score++;
            if (/[^a-zA-Z\d]/.test(password)) score++;

            const strengthLevels = [
                { text: 'Très faible', class: 'text-danger', percentage: 20 },
                { text: 'Faible', class: 'text-warning', percentage: 40 },
                { text: 'Moyen', class: 'text-info', percentage: 60 },
                { text: 'Fort', class: 'text-success', percentage: 80 },
                { text: 'Très fort', class: 'text-success', percentage: 100 },
            ];

            passwordStrength.value = {
                score,
                ...strengthLevels[score] || strengthLevels[0]
            };
        };

        const triggerAvatarUpload = () => {
            if (avatarInput.value) {
                avatarInput.value.click();
            }
        };

        const handleAvatarUpload = async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'Fichier trop volumineux',
                    text: 'La taille maximale autorisée est de 5MB',
                });
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);

            try {
                await axios.post('/profile/avatar', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                // Refresh user data
                await fetchUserData();

                Swal.fire({
                    icon: 'success',
                    title: 'Succès!',
                    text: 'Avatar mis à jour avec succès',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors du téléchargement de l\'avatar',
                });
            }
        };

        const sendVerificationEmail = async () => {
            verificationLoading.value = true;

            try {
                await axios.post('/email/verification-notification');

                Swal.fire({
                    icon: 'success',
                    title: 'Email envoyé!',
                    text: 'Un email de vérification a été envoyé à votre adresse',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors de l\'envoi de l\'email de vérification',
                });
            } finally {
                verificationLoading.value = false;
            }
        };

        const downloadData = async () => {
            try {
                const response = await axios.get('/profile/export', {
                    responseType: 'blob'
                });

                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `mes-donnees-${new Date().toISOString().split('T')[0]}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors du téléchargement du PDF',
                });
            }
        };

        const confirmLogout = () => {
            Swal.fire({
                title: 'Se déconnecter?',
                text: 'Êtes-vous sûr de vouloir vous déconnecter?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, se déconnecter',
                cancelButtonText: 'Annuler'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        await axios.post('/logout');
                        window.location.href = '/login';
                    } catch (error) {
                        console.error('Logout error:', error);
                    }
                }
            });
        };

        const formatDate = (dateString) => {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        };

        const formatDateTime = (dateString) => {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleString('fr-FR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        // Lifecycle
        onMounted(() => {
            fetchUserData();
        });

        return {
            user,
            isEditingProfile,
            passwordMode,
            profileForm,
            passwordForm,
            forgotPasswordForm,
            profileErrors,
            passwordErrors,
            forgotPasswordErrors,
            profileLoading,
            passwordLoading,
            forgotPasswordLoading,
            verificationLoading,
            forgotPasswordSuccess,
            showCurrentPassword,
            showNewPassword,
            showConfirmPassword,
            passwordStrength,
            avatarInput,
            avatarUrl,
            isPasswordFormValid,
            setPasswordMode,
            toggleProfileEdit,
            updateProfile,
            updatePassword,
            sendPasswordResetLink,
            resetPasswordForm,
            resetForgotPasswordForm,
            checkPasswordStrength,
            triggerAvatarUpload,
            handleAvatarUpload,
            sendVerificationEmail,
            downloadData,
            confirmLogout,
            formatDate,
            formatDateTime,
        };
    },
};
</script>

<style scoped>
/* Tous les styles CSS précédents restent identiques */
/* Modern Header Styles */
.modern-header {
    background: linear-gradient(135deg, #ABC270 0%, #8FA55C 100%);
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(171, 194, 112, 0.3);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
}

.title-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 12px;
    backdrop-filter: blur(10px);
}

.icon-wrapper i {
    font-size: 1.5rem;
}

.title-content h4 {
    color: white;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.page-subtitle {
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    font-size: 0.95rem;
}

.user-info-badge {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255, 255, 255, 0.15);
    padding: 0.75rem 1rem;
    border-radius: 50px;
    backdrop-filter: blur(10px);
}

.avatar-wrapper {
    position: relative;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.status-indicator {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.status-indicator.online {
    background-color: #28a745;
}

.status-indicator.offline {
    background-color: #dc3545;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.user-role {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.8rem;
}

/* Profile Container */
.profile-container {
    max-width: 100%;
}

/* Modern Card Styles */
.modern-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.modern-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.modern-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
    padding: 1.25rem 1.5rem;
}

.modern-card-header .card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
}

.modern-card-body {
    padding: 2rem 1.5rem;
}

/* Profile Card Styles */
.profile-card .modern-card-body {
    padding: 2rem 1.5rem;
}

.profile-avatar-section {
    margin-bottom: 1.5rem;
}

.avatar-container {
    position: relative;
    display: inline-block;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid #ABC270;
    box-shadow: 0 4px 20px rgba(171, 194, 112, 0.3);
}

.avatar-edit-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: none;
    background: #ABC270;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(171, 194, 112, 0.4);
}

.avatar-edit-btn:hover:not(:disabled) {
    background: #8FA55C;
    transform: scale(1.1);
}

.avatar-edit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.profile-email {
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    margin-bottom: 1.5rem;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 0.9rem;
    font-weight: 600;
    color: #495057;
}

.stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.verification-section {
    margin-top: 1rem;
}

/* Quick Actions */
.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border: none;
    background: #f8f9fa;
    border-radius: 10px;
    color: #495057;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.action-btn:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.action-btn.text-danger:hover {
    background: #f8d7da;
    color: #721c24;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    font-size: 0.95rem;
}

.form-label.required::after {
    content: " *";
    color: #dc3545;
    font-weight: bold;
}

.form-label i {
    color: #6c757d;
    margin-right: 0.5rem;
}

.modern-input {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: #fff;
}

.modern-input:focus {
    border-color: #ABC270;
    box-shadow: 0 0 0 0.2rem rgba(171, 194, 112, 0.25);
    background-color: #fff;
}

.form-control-readonly {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
}

.form-control-readonly:focus {
    background-color: #f8f9fa;
    border-color: #e9ecef;
    box-shadow: none;
}

/* Validation Styles */
.is-valid {
    border-color: #28a745;
}

.is-valid:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.is-invalid {
    border-color: #dc3545;
}

.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

/* Password Input Group */
.password-input-group {
    position: relative;
}

.password-toggle-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: color 0.3s ease;
}

.password-toggle-btn:hover {
    color: #495057;
}

/* Password Strength */
.password-strength {
    margin-top: 0.5rem;
}

.strength-bar {
    height: 4px;
    background-color: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 0.25rem;
}

.strength-fill {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-fill.text-danger {
    background-color: #dc3545;
}

.strength-fill.text-warning {
    background-color: #ffc107;
}

.strength-fill.text-info {
    background-color: #17a2b8;
}

.strength-fill.text-success {
    background-color: #28a745;
}

.strength-text {
    font-size: 0.85rem;
    font-weight: 500;
}

/* Modern Button Styles */
.modern-btn {
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modern-btn:active {
    transform: translateY(0);
}

.modern-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Button Group Styles */
.btn-group .modern-btn {
    border-radius: 0;
}

.btn-group .modern-btn:first-child {
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;
}

.btn-group .modern-btn:last-child {
    border-top-right-radius: 10px;
    border-bottom-right-radius: 10px;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Alert Styles */
.modern-alert {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: flex-start;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    border-left: 4px solid #ffc107;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border-left: 4px solid #28a745;
}

/* Security Info Styles */
.security-info, .forgot-password-info {
    margin-bottom: 1.5rem;
}

/* Account Info */
.account-info {
    padding: 1rem 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.info-item:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.info-content {
    flex: 1;
}

.info-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.info-value {
    color: #6c757d;
    font-size: 0.85rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .user-info-badge {
        flex-direction: column;
        text-align: center;
    }

    .modern-card-body {
        padding: 1.5rem 1rem;
    }

    .profile-stats {
        flex-direction: column;
        gap: 1rem;
    }

    .form-actions {
        flex-direction: column;
    }

    .modern-btn {
        width: 100%;
        justify-content: center;
    }

    .btn-group {
        flex-direction: column;
        width: 100%;
    }

    .btn-group .modern-btn {
        border-radius: 10px;
        margin-bottom: 0.5rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .modern-header {
        margin: 0 -15px 2rem -15px;
        border-radius: 0;
    }

    .header-content {
        padding: 1rem;
    }

    .title-content h4 {
        font-size: 1.25rem;
    }

    .modern-card {
        margin: 0 -15px 1rem -15px;
        border-radius: 0;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
    }
}

/* Animation */
.profile-container {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Focus indicators for accessibility */
.modern-input:focus,
.modern-btn:focus,
.password-toggle-btn:focus {
    outline: none;
}

.modern-input:focus-visible,
.modern-btn:focus-visible,
.password-toggle-btn:focus-visible {
    outline: 2px solid #ABC270;
    outline-offset: 2px;
}
</style>

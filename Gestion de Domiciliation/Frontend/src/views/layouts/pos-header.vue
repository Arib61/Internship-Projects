<template>
    <!-- Header -->
    <div class="header">

        <!-- Logo -->
        <div class="header-left active">
            <router-link to="/dashboard" class="logo logo-normal">
                <img src="@/assets/img/logo.png" alt="">
            </router-link>
            <router-link to="/dashboard" class="logo logo-white">
                <img src="@/assets/img/logo.png" alt="">
            </router-link>
            <router-link to="/dashboard" class="logo_small">
                <img src="@/assets/img/logo_small.png" alt="">
            </router-link>
            <a id="toggle_btn" href="javascript:void(0);" @click="toggleSidebar">
                <vue-feather type="chevrons-left"></vue-feather>
            </a>
        </div>
        <!-- /Logo -->

        <a id="mobile_btn" class="mobile_btn" href="javascript:void(0);" @click="toggleSidebar1">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </a>

        <!-- Header Menu -->
    <ul class="nav user-menu">
      <!-- Search -->
      <li class="nav-item nav-searchinputs">
        <div class="top-nav-search">
          <a href="javascript:void(0);" class="responsive-search">
            <i class="fa fa-search"></i>
          </a>
          <form action="javascript:void(0);" class="dropdown">
            <div
              class="searchinputs dropdown-toggle"
              id="dropdownMenuClickable"
              data-bs-toggle="dropdown"
              data-bs-auto-close="false"
            >
              <input type="text" placeholder="Search" disabled />
              <div class="search-addon">
                <span
                  ><vue-feather type="x-circle" class="feather-14"></vue-feather
                ></span>
              </div>
            </div>
            <div
              class="dropdown-menu search-dropdown"
              aria-labelledby="dropdownMenuClickable"
            >
              <div class="search-info">
                <h6>
                  <span
                    ><vue-feather
                      type="search"
                      class="feather-16"
                    ></vue-feather></span
                  >Recent Searches
                </h6>
                <ul class="search-tags">
                  <li><a href="javascript:void(0);">Products</a></li>
                  <li><a href="javascript:void(0);">Sales</a></li>
                  <li><a href="javascript:void(0);">Applications</a></li>
                </ul>
              </div>
              <div class="search-info">
                <h6>
                  <span
                    ><vue-feather
                      type="help-circle"
                      class="feather-16"
                    ></vue-feather></span
                  >Help
                </h6>
                <p>
                  How to Change Product Volume from 0 to 200 on Inventory
                  management
                </p>
                <p>Change Product Name</p>
              </div>
              <div class="search-info">
                <h6>
                  <span
                    ><vue-feather
                      type="user"
                      class="feather-16"
                    ></vue-feather></span
                  >Customers
                </h6>
                <ul class="customers">
                  <li>
                    <a href="javascript:void(0);"
                      >Aron Varu<img
                        src="@/assets/img/profiles/avator1.jpg"
                        alt=""
                        class="img-fluid"
                    /></a>
                  </li>
                  <li>
                    <a href="javascript:void(0);"
                      >Jonita<img
                        src="@/assets/img/profiles/avatar-01.jpg"
                        alt=""
                        class="img-fluid"
                    /></a>
                  </li>
                  <li>
                    <a href="javascript:void(0);"
                      >Aaron<img
                        src="@/assets/img/profiles/avatar-10.jpg"
                        alt=""
                        class="img-fluid"
                    /></a>
                  </li>
                </ul>
              </div>
            </div>
          </form>
        </div>
      </li>
      <!-- /Search -->

      <li class="nav-item dropdown has-arrow main-drop">
        <a
          href="javascript:void(0);"
          class="dropdown-toggle nav-link userset"
          data-bs-toggle="dropdown"
        >
          <span class="user-info">
            <span class="user-letter">
              <img
                src="@/assets/img/user.png"
                alt=""
                class="img-fluid"
              />
            </span>
            <span class="user-detail">
              <span class="user-name">user-name</span>
              <span class="user-role">dwada</span>
            </span>
          </span>
        </a>
        <div class="dropdown-menu menu-drop-user">
          <div class="profilename">
            <div class="profileset">
              <span class="user-img"
                ><img src="@/assets/img/user.png" alt="" />
                <span class="status online"></span
              ></span>
              <div class="profilesets">
                <h6 class="user-name">John Smilga</h6>
                <h5 class="user-role">Super Admin</h5>
              </div>
            </div>
            <hr class="m-0" />
            <router-link class="dropdown-item" to="/profile">
              <vue-feather class="me-2" type="user"></vue-feather> Mon
              Profile</router-link
            >
            <router-link class="dropdown-item" to="/settings"
              ><vue-feather class="me-2" type="settings"></vue-feather
              >Paramètres</router-link
            >
            <hr class="m-0" />
            <router-link class="dropdown-item logout pb-0" to="/logout"
              ><img
                src="@/assets/img/icons/log-out.svg"
                class="me-2"
                alt="img"
              />Logout</router-link
            >
          </div>
        </div>
      </li>
    </ul>
    <!-- /Header Menu -->

    <!-- Mobile Menu -->
    <div class="dropdown mobile-user-menu">
      <a
        href="javascript:void(0);"
        class="nav-link dropdown-toggle"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        ><i class="fa fa-ellipsis-v"></i
      ></a>
      <div class="dropdown-menu dropdown-menu-right">
        <router-link class="dropdown-item" to="/profile"
          >Mon Profile</router-link
        >
        <router-link class="dropdown-item" to="/settings">Paramètres</router-link>
        <router-link class="dropdown-item" to="/logout">Logout</router-link>
      </div>
    </div>
    <!-- /Mobile Menu -->
  </div>
  <!-- /Header -->

  <side-settings></side-settings>
</template>

<script>
export default {
  data() {
    return {};
  },
  mounted() {
    // To retrieve username and role from localStorage:
    const userData = localStorage.getItem("user");
    let username = "";
    let role = "";

    if (userData) {
      const user = JSON.parse(userData);
      username = user.username;
      role = user.role;
    }

    document.querySelectorAll(".user-name")[0].textContent = username;
    document.querySelectorAll(".user-name")[1].textContent = username;
    document.querySelectorAll(".user-role")[0].textContent = role;
    document.querySelectorAll(".user-role")[1].textContent = role;
    this.initMouseoverListener();
  },
  methods: {
    toggleSidebar1() {
      const body = document.body;
      body.classList.toggle("slide-nav");
    },
    toggleSidebar() {
      const body = document.body;
      body.classList.toggle("mini-sidebar");
    },
    initFullScreen() {
      document.body.classList.toggle("fullscreen-enable");
      if (
        !document.fullscreenElement &&
        /* alternative standard method */
        !document.mozFullScreenElement &&
        !document.webkitFullscreenElement
      ) {
        // current working methods
        if (document.documentElement.requestFullscreen) {
          document.documentElement.requestFullscreen();
        } else if (document.documentElement.mozRequestFullScreen) {
          document.documentElement.mozRequestFullScreen();
        } else if (document.documentElement.webkitRequestFullscreen) {
          document.documentElement.webkitRequestFullscreen(
            Element.ALLOW_KEYBOARD_INPUT
          );
        }
      } else {
        if (document.cancelFullScreen) {
          document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
          document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
          document.webkitCancelFullScreen();
        }
      }
    },

    initMouseoverListener() {
      document.addEventListener("mouseover", this.handleMouseover);
    },
    handleMouseover(e) {
      e.stopPropagation();

      const body = document.body;
      const toggleBtn = document.getElementById("toggle_btn");

      if (
        body.classList.contains("mini-sidebar") &&
        this.isElementVisible(toggleBtn)
      ) {
        const target = e.target.closest(".sidebar, .header-left");

        if (target) {
          body.classList.add("expand-menu");
          this.slideDownSubmenu();
        } else {
          body.classList.remove("expand-menu");
          this.slideUpSubmenu();
        }

        e.preventDefault();
      }
    },
    isElementVisible(element) {
      return element.offsetWidth > 0 || element.offsetHeight > 0;
    },
    slideDownSubmenu() {
      const subdropPlusUl = document.getElementsByClassName("subdrop");
      for (let i = 0; i < subdropPlusUl.length; i++) {
        const submenu = subdropPlusUl[i].nextElementSibling;
        if (submenu && submenu.tagName.toLowerCase() === "ul") {
          submenu.style.display = "block";
        }
      }
    },
    slideUpSubmenu() {
      const subdropPlusUl = document.getElementsByClassName("subdrop");
      for (let i = 0; i < subdropPlusUl.length; i++) {
        const submenu = subdropPlusUl[i].nextElementSibling;
        if (submenu && submenu.tagName.toLowerCase() === "ul") {
          submenu.style.display = "none";
        }
      }
    },
  },
  beforeUnmount() {
    document.removeEventListener("mouseover", this.handleMouseover);
  },
};
</script>
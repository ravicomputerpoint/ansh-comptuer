<?php require_once __DIR__ . '/../config.php'; ?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ansh Computer | Dashboard</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;

        // Applications with their own theming opt out of AdminLTE's color mode
        // entirely, here as well as in the bundle.
        if (root.getAttribute('data-lte-color-mode') === 'off') {
          return;
        }

        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
          stored = localStorage.getItem(STORAGE_KEY);
        } catch {
          // localStorage may be unavailable (private mode, sandboxed iframe).
        }
        // Mirror the precedence in color-mode.ts: the visitor's stored choice
        // wins, then a theme this page declared itself, then the OS preference.
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
          resolved = stored;
        } else if (authored === 'dark' || authored === 'light') {
          resolved = authored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
          resolved = 'dark';
        }
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
        // Flag values computed here, so the bundle does not mistake them for a
        // theme the page declared and stop following the OS preference.
        if (resolved !== authored) {
          root.setAttribute('data-lte-theme-resolved', '');
        }
      })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Features-->
    <!-- Skip links will be dynamically added by accessibility.js -->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="stylesheet" href="<?= BASE_URL ?>css/adminlte.css">
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.3.0/index.css"
      integrity="sha256-5UwXtC6TaPHCAbVQ59waEAqOssHyhn/twyuLRRWszzo="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->

    <!-- Datatables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/v/dt/dt-3.1.5/datatables.min.css">
    <script src="https://cdn.datatables.net/v/dt/dt-3.1.5/datatables.min.js"></script>
  </head>
  <!--end::Head-->
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <!--begin::Container-->
        <div class="container-fluid">
          <!--begin::Start Navbar Links-->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a
                class="nav-link"
                data-lte-toggle="sidebar"
                href="#"
                role="button"
                aria-label="Toggle sidebar"
              >
                <i class="bi bi-list"></i>
              </a>
            </li>
          </ul>
          <!--end::Start Navbar Links-->
        </div>
        <!--end::Container-->
      </nav>
      <!--end::Header-->
      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="index.php" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="<?= BASE_URL ?>assets/img/AdminLTELogo.png"
              alt="AdminLTE Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-light">Ansh Computer</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->
        <!--begin::Sidebar Search-->
        <div class="sidebar-search" role="search">
          <label for="sidebar-search-input" class="visually-hidden">Filter menu</label>
          <input
            type="search"
            id="sidebar-search-input"
            class="form-control form-control-sm"
            placeholder="Filter menu…"
            autocomplete="off"
            data-lte-toggle="sidebar-search"
            data-lte-target="#navigation"
          />
          <p class="fs-7 text-secondary mt-2 mb-0" data-lte-search-empty role="status" hidden>
            No matching pages.
          </p>
        </div>
        <!--end::Sidebar Search-->
        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2" aria-label="Main navigation">
            <!--begin::Sidebar Menu-->
            <?php
                $current_page = $_SERVER['PHP_SELF'];

                function active($path) {
                    global $current_page;

                    return str_ends_with($current_page, $path) ? 'active' : '';
                }
            ?>

            <ul class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                data-accordion="false"
                id="navigation">

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>index.php"
                      class="nav-link <?= active('/ansh2/index.php') ?>">
                        <i class="nav-icon bi bi-house-fill"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-header">TRANSACTIONS</li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>purchases/index.php"
                      class="nav-link <?= active('/purchases/index.php') ?>">
                        <i class="nav-icon bi bi-bag-heart-fill"></i>
                        <p>Purchases</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>sales/index.php"
                      class="nav-link <?= active('/sales/index.php') ?>">
                        <i class="nav-icon bi bi-tags-fill"></i>
                        <p>Sales</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>receipts/index.php"
                      class="nav-link <?= active('/receipts/index.php') ?>">
                        <i class="nav-icon bi bi-receipt"></i>
                        <p>Receipts</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>payments/index.php"
                      class="nav-link <?= active('/payments/index.php') ?>">
                        <i class="nav-icon bi bi-credit-card"></i>
                        <p>Payments</p>
                    </a>
                </li>

                <li class="nav-header">SETTINGS</li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>suppliers/index.php"
                      class="nav-link <?= active('/suppliers/index.php') ?>">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>Suppliers</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>customers/index.php"
                      class="nav-link <?= active('/customers/index.php') ?>">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>Customers</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= BASE_URL ?>items/index.php"
                      class="nav-link <?= active('/items/index.php') ?>">
                        <i class="nav-icon bi bi-list-stars"></i>
                        <p>Items</p>
                    </a>
                </li>

            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
          <div class="app-content-header">
            <!--begin::Container-->
            <div class="container-fluid">
              <!--begin::Row-->
              <div class="row">
                <div class="col-sm-6">
                  <h1 class="mb-0 fs-3"><?php echo $page_title ?></h1>
                </div>
              </div>
              <!--end::Row-->
            </div>
            <!--end::Container-->
          </div>
          <!--end::App Content Header-->
          <!--begin::App Content-->
          <div class="app-content">
            <!--begin::Container-->
            <div class="container-fluid">
              <!--begin::Row-->
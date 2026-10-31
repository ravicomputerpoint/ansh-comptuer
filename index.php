<?php 
  $page_title = "Dashboard";
  include('includes/header.php'); 
?>
<div class="row">

    <!-- Total Purchase -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-primary">
            <div class="inner">
                <h3>150</h3>
                <p>Total Purchase</p>
            </div>

            <i class="small-box-icon bi bi-bag-heart-fill"></i>

            <a href="<?= BASE_URL ?>purchases/index.php"
               class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                More info <i class="bi bi-link-45deg"></i>
            </a>
        </div>
    </div>

    <!-- Total Sales -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success">
            <div class="inner">
                <h3>53</h3>
                <p>Total Sales</p>
            </div>

            <i class="small-box-icon bi bi-tags-fill"></i>

            <a href="<?= BASE_URL ?>sales/index.php"
               class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                More info <i class="bi bi-link-45deg"></i>
            </a>
        </div>
    </div>

    <!-- Total Receipts -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-warning">
            <div class="inner">
                <h3>44</h3>
                <p>Total Receipts</p>
            </div>

            <i class="small-box-icon bi bi-receipt"></i>

            <a href="<?= BASE_URL ?>receipts/index.php"
               class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                More info <i class="bi bi-link-45deg"></i>
            </a>
        </div>
    </div>

    <!-- Total Payments -->
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-danger">
            <div class="inner">
                <h3>65</h3>
                <p>Total Payments</p>
            </div>

            <i class="small-box-icon bi bi-credit-card"></i>

            <a href="<?= BASE_URL ?>payments/index.php"
               class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                More info <i class="bi bi-link-45deg"></i>
            </a>
        </div>
    </div>

</div>
<!--end::Row-->
</div>
<!--end::Container-->
</div>
<!--end::App Content-->
<?php include('includes/footer.php') ?>

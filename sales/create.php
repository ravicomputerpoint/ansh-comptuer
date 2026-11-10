<?php

$page_title = "Create New Sale";

include('../includes/header.php');
include('../db_config.php');

$customers = mysqli_query($conn, "SELECT id, name FROM customers ORDER BY name");
$items = mysqli_query($conn, "SELECT id, name FROM items ORDER BY name");
$invoice_no = mysqli_fetch_assoc(mysqli_query($conn, "SELECT invoice_no from sales order by invoice_no desc LIMIT 1"));
?>

<style>
    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 38px;
    }

    .select2-container--default .select2-selection--single
    .select2-selection__rendered {
        line-height: 36px;
    }

    .select2-container--default .select2-selection--single
    .select2-selection__arrow {
        height: 36px;
    }
</style>


<div class="text-end mb-3">

    <a href="index.php" class="btn btn-danger">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>

</div>


<form action="save.php" method="POST" id="purchaseForm">


    <!-- Sale Details -->

    <div class="row g-3 align-items-end">

        <div class="col-md-4">

            <label for="date" class="form-label">Date</label>

            <input type="date"
                   class="form-control"
                   id="date"
                   name="date"
                   value="<?= date('Y-m-d') ?>"
                   required>

        </div>


        <div class="col-md-4">

            <label for="invoice_no" class="form-label">
                Invoice No.
            </label>

            <input type="text"
                   class="form-control"
                   id="invoice_no"
                   name="invoice_no"
                   value="<?php echo $invoice_no['invoice_no'] + 1 ?? '1' ?>">

        </div>


        <div class="col-md-4">

            <label for="customer_id" class="form-label">
                Select Customer
            </label>

            <select class="form-control"
                    name="customer_id"
                    id="customer_id"
                    required>

                <option value="">Select Customer</option>

                <?php while ($customer = mysqli_fetch_assoc($customers)): ?>

                    <option value="<?= $customer['id'] ?>">
                        <?= htmlspecialchars($customer['name']) ?>
                    </option>

                <?php endwhile; ?>

            </select>

        </div>

    </div>


    <hr>


    <!-- Sale Items -->

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h5 class="mb-0">Sale Items</h5>

        <button type="button"
                class="btn btn-primary btn-sm"
                id="addItem">

            <i class="bi bi-plus-lg me-1"></i>
            Add Item

        </button>

    </div>


    <div class="table-responsive">

        <table class="table table-bordered align-middle">

            <thead>

                <tr>
                    <th style="min-width:200px">Item Name</th>
                    <th style="min-width:100px">Quantity</th>
                    <th style="min-width:120px">Rate</th>
                    <th style="min-width:120px">Total</th>
                    <th>Action</th>
                </tr>

            </thead>


            <tbody id="saleItems">


                <!-- First Row -->

                <tr class="purchase-row">

                    <td>

                        <select name="items[]"
                                class="form-control item-select"
                                required>

                            <option value="">Select Item</option>

                            <?php
                            mysqli_data_seek($items, 0);

                            while ($item = mysqli_fetch_assoc($items)):
                            ?>

                                <option value="<?= $item['id'] ?>">
                                    <?= htmlspecialchars($item['name']) ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                    </td>


                    <td>

                        <input type="number"
                               name="quantities[]"
                               class="form-control quantity"
                               min="0.01"
                               step="any"
                               value="1"
                               required>

                    </td>


                    <td>

                        <input type="number"
                               name="rates[]"
                               class="form-control rate"
                               min="0"
                               step="any"
                               value="0"
                               required>

                    </td>


                    <td>

                        <span class="row-total">0.00</span>

                    </td>


                    <td>

                        <button type="button"
                                class="btn btn-danger remove-item">

                            <i class="bi bi-trash"></i>

                        </button>

                    </td>

                </tr>


            </tbody>


            <tfoot>

                <tr>

                    <th>Grand Total</th>

                    <th>
                        <span id="totalQuantities">1</span>
                    </th>

                    <th></th>

                    <th>
                        <span id="grandTotal">0.00</span>
                    </th>

                    <th></th>

                </tr>

            </tfoot>

        </table>

    </div>


    <button type="submit" class="btn btn-success">

        <i class="bi bi-save me-2"></i>
        Save Sale

    </button>


</form>



<!-- New Row Template -->

<template id="itemTemplate">

    <tr class="purchase-row">

        <td>

            <select name="items[]"
                    class="form-control item-select"
                    required>

                <option value="">Select Item</option>

                <?php
                mysqli_data_seek($items, 0);

                while ($item = mysqli_fetch_assoc($items)):
                ?>

                    <option value="<?= $item['id'] ?>">
                        <?= htmlspecialchars($item['name']) ?>
                    </option>

                <?php endwhile; ?>

            </select>

        </td>


        <td>

            <input type="number"
                   name="quantities[]"
                   class="form-control quantity"
                   min="0.01"
                   step="any"
                   value="1"
                   required>

        </td>


        <td>

            <input type="number"
                   name="rates[]"
                   class="form-control rate"
                   min="0"
                   step="any"
                   value="0"
                   required>

        </td>


        <td>

            <span class="row-total">0.00</span>

        </td>


        <td>

            <button type="button"
                    class="btn btn-danger remove-item">

                <i class="bi bi-trash"></i>

            </button>

        </td>

    </tr>

</template>



<script>

$(document).ready(function() {


    // Initialize Select2

    function initSelect2(element, placeholder) {

        $(element).select2({

            width: '100%',

            placeholder: placeholder,

            allowClear: true

        });

    }


    // Initialize Supplier Select2

    initSelect2('#customer_id', 'Select Customer');


    // Initialize First Row Select2

    initSelect2(
        $('#saleItems .item-select'),
        'Select Item'
    );



    // Add New Item Row

    $('#addItem').on('click', function() {

        let template = document.getElementById('itemTemplate');

        // Create fresh HTML row
        let newRow = template.content.cloneNode(true);

        // Append new row
        $('#saleItems').append(newRow);

        // Get newly created row
        let lastRow = $('#saleItems .purchase-row:last');

        // Initialize Select2 ONLY on new row
        initSelect2(
            lastRow.find('.item-select'),
            'Select Item'
        );

        calculateGrandTotal();

    });



    // Remove Item Row

    $('#saleItems').on('click', '.remove-item', function() {

        if ($('.purchase-row').length > 1) {

            // Destroy Select2 before removing
            let select = $(this).closest('tr').find('.item-select');

            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }

            $(this).closest('tr').remove();

            calculateGrandTotal();

        }

    });



    // Calculate Row Total

    $('#saleItems').on('input', '.quantity, .rate', function() {

        let row = $(this).closest('tr');

        let quantity = parseFloat(row.find('.quantity').val()) || 0;

        let rate = parseFloat(row.find('.rate').val()) || 0;

        let total = quantity * rate;

        row.find('.row-total').text(total.toFixed(2));

        calculateGrandTotal();

    });



    // Calculate Grand Total

    function calculateGrandTotal() {

        let grandTotal = 0;

        let totalQuantities = 0;


        $('.purchase-row').each(function() {

            let quantity = parseFloat(
                $(this).find('.quantity').val()
            ) || 0;

            let rate = parseFloat(
                $(this).find('.rate').val()
            ) || 0;


            let total = quantity * rate;

            grandTotal += total;

            totalQuantities += quantity;


            $(this).find('.row-total').text(
                total.toFixed(2)
            );

        });


        $('#grandTotal').text(grandTotal.toFixed(2));

        $('#totalQuantities').text(totalQuantities);

    }


});


</script>


<?php include('../includes/footer.php'); ?>

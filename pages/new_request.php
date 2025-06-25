<link rel="stylesheet" href="../css/new_request.css">
<?php
include("../includes/auth.php");
include("../includes/db.php");
include("../includes/header.php");

// Fetch Executive Officers
$executives = $pdo->query("SELECT id, full_name FROM users WHERE role_id = 2")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Locations
$locations = $pdo->query("SELECT id, location_name FROM locations")->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<h2>New Gate Pass Request</h2>

<form action="../process/new_request_process.php" method="POST" enctype="multipart/form-data" id="requestForm">

    <!-- Sender Details -->
    <div class="section-box">
        <h3>Sender Details</h3>
        <label>Sender User ID:</label>
        <input type="text" name="sender_user_id" id="sender_user_id" required>
        <label>Sender Name:</label>
        <input type="text" name="sender_name" id="sender_name" readonly>
        <label>Working Location:</label>
        <input type="text" name="sender_work_location" id="sender_work_location" readonly>
        <label>Role:</label>
        <input type="text" name="sender_role" id="sender_role" readonly>
        <label>Contact Number:</label>
        <input type="text" name="sender_contact" id="sender_contact" readonly>
    </div>

    <!-- Receiver Details -->
    <div class="section-box">
        <h3>Receiver Details</h3>
        <label>Receiver User ID:</label>
        <input type="text" name="receiver_user_id" id="receiver_user_id" required>
        <label>Receiver Name:</label>
        <input type="text" name="receiver_name" id="receiver_name" readonly>
        <label>Working Location:</label>
        <input type="text" name="receiver_work_location" id="receiver_work_location" readonly>
        <label>Role:</label>
        <input type="text" name="receiver_role" id="receiver_role" readonly>
        <label>Contact Number:</label>
        <input type="text" name="receiver_contact" id="receiver_contact" readonly>
    </div>

    <!-- Item Details -->
    <div class="section-box">
        <h3>Item Details</h3>
        <table id="itemsTable" border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Returnable</th>
                    <th>Item Photos (Max 5)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- JS will add rows -->
            </tbody>
        </table>
        <button type="button" id="addItemBtn">Add Item</button>
    </div>

    <!-- Transport Details -->
    <div class="section-box">
        <h3>Transport Details</h3>
        <label>Transport Method:</label>
        <input type="radio" name="transport_method" value="person" checked> By Person
        <input type="radio" name="transport_method" value="vehicle"> By Vehicle

        <div id="by_person">
            <label>Person Name:</label>
            <input type="text" name="person_name">
            <label>Address:</label>
            <input type="text" name="person_address">
            <label>NIC:</label>
            <input type="text" name="person_nic">
            <label>Contact Number:</label>
            <input type="text" name="person_contact">
        </div>

        <div id="by_vehicle" style="display: none;">
            <label>Driver Name:</label>
            <input type="text" name="driver_name">
            <label>Vehicle No:</label>
            <input type="text" name="vehicle_no">
            <label>Contact Number:</label>
            <input type="text" name="vehicle_contact">
        </div>
    </div>

    <!-- Executive Approval -->
    <div class="section-box">
        <h3>Executive Approval</h3>
        <label>Select Executive:</label>
        <select name="executive_id" required>
            <option value="">-- Select Executive --</option>
            <?php foreach ($executives as $exec): ?>
                <option value="<?= $exec['id']; ?>"><?= htmlspecialchars($exec['full_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- In & Out Locations -->
    <div class="section-box">
        <h3>In Location</h3>
        <label>Select In Location:</label>
        <select name="in_location_id" required>
            <option value="">-- Select In Location --</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?= $loc['id']; ?>"><?= htmlspecialchars($loc['location_name']); ?></option>
            <?php endforeach; ?>
        </select>

        <h3>Out Location</h3>
        <label>Select Out Location:</label>
        <select name="out_location_id" required>
            <option value="">-- Select Out Location --</option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?= $loc['id']; ?>"><?= htmlspecialchars($loc['location_name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <input type="submit" value="Submit Request">
</form>

<script>
$(document).ready(function () {
    let itemIndex = 0;

    $('#addItemBtn').click(function () {
        const row = `
        <tr>
            <td><input type="text" name="serial_no[]" required></td>
            <td><input type="text" name="item_name[]" required></td>
            <td><input type="number" name="quantity[]" min="1" required></td>
            <td>
                <select name="is_returnable[]">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </td>
            <td>
                ${[0,1,2,3,4].map(i => `<input type="file" name="item_photos[${itemIndex}][]" accept="image/*">`).join('<br>')}
            </td>
            <td><button type="button" class="removeItemBtn">Remove</button></td>
        </tr>`;
        $('#itemsTable tbody').append(row);
        itemIndex++;
    });

    $(document).on('click', '.removeItemBtn', function () {
        $(this).closest('tr').remove();
    });

    $('input[name="transport_method"]').on('change', function () {
        if ($(this).val() === 'person') {
            $('#by_person').show();
            $('#by_vehicle').hide();
        } else {
            $('#by_person').hide();
            $('#by_vehicle').show();
        }
    });

    $('#sender_user_id').on('blur', function () {
        $.post('../process/get_user_details.php', { user_id: $(this).val() }, function (data) {
            if (data.success) {
                $('#sender_name').val(data.full_name);
                $('#sender_work_location').val(data.work_location);
                $('#sender_role').val(data.role);
                $('#sender_contact').val(data.contact_number);
            }
        }, 'json');
    });

    $('#receiver_user_id').on('blur', function () {
        $.post('../process/get_user_details.php', { user_id: $(this).val() }, function (data) {
            if (data.success) {
                $('#receiver_name').val(data.full_name);
                $('#receiver_work_location').val(data.work_location);
                $('#receiver_role').val(data.role);
                $('#receiver_contact').val(data.contact_number);
            }
        }, 'json');
    });
});
</script>

<?php include("../includes/footer.php"); ?>

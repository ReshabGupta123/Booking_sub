<?php
$property_id = $_GET['id'] ?? null;
if (!$property_id) {
    die("Property ID is missing.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hotel Booking Form</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
  <style>
    .form-container { max-width: 600px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 16px; }
    label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
    select, input[type="number"], input[type="text"], input[type="email"], input[type="date"] {
      width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
    }
    .price-display { margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 4px; font-size: 1.2em; font-weight: 600; text-align: center; }
    .razorpay-button { display: block; width: 100%; padding: 12px; margin-top: 20px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 1.1em; cursor: pointer; transition: background 0.3s; }
    .razorpay-button:hover { background: #0056b3; }
  </style>
</head>

<body class="bg-gray-100">

<div class="form-container">
  <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Book Your Stay</h2>

  <form id="bookingForm" action="insert_booking.php" method="POST">
    <!-- Hidden Settings -->
    <!-- Then insert it into a hidden input in the booking form -->
    <input type="hidden" name="property_id" value="<?= htmlspecialchars($property_id) ?>">
    <input type="hidden" name="_subject" value="New Hotel Booking Request">
    <input type="hidden" name="_captcha" value="false">
<!-- Guest Details -->
<div class="form-group">
  <label for="guestName">Full Name:</label>
  <input type="text" id="guestName" name="Name" required>
</div>

<div class="form-group">
  <label for="guestEmail">Email:</label>
  <input type="email" id="guestEmail" name="Email" required>
</div>

<div class="form-group">
  <label for="guestPhone">Phone Number:</label>
  <input type="text" id="guestPhone" name="Phone" required>
</div>

<!-- Check-in and Check-out -->
<div class="form-group">
  <label for="checkin">Check-in Date:</label>
  <input type="date" id="checkin" name="Check-in" required>
</div>

<div class="form-group">
  <label for="checkout">Check-out Date:</label>
  <input type="date" id="checkout" name="Check-out" required>
</div>

<!-- Occupancy -->
<div class="form-group">
  <label for="occupancy">Occupancy:</label>
  <select id="occupancy" name="Occupancy">
    <option value="Single">Single</option>
    <option value="Double">Double</option>
  </select>
</div>

<!-- Package Type -->
<div class="form-group">
  <label for="packageType">Package Type:</label>
  <select id="packageType" name="Package Type">
    <option value="CP">Continental Plan (CP)</option>
    <option value="MAP">Modified American Plan (MAP)</option>
  </select>
</div>

<!-- Nights -->
<div class="form-group">
  <label for="nights">Number of Nights:</label>
  <input type="number" id="nights" name="Nights" min="1" value="1" readonly>
</div>

<!-- Extra Person Options -->
<div id="extraPersonOptions">
  <div class="form-group">
    <input type="checkbox" id="addExtraPerson" name="Add Extra Person" class="mr-2">
    <label for="addExtraPerson" class="inline-block">Add Extra Person?</label>
  </div>
  <div class="form-group" id="extraPersonGroup" style="display: none;">
    <label for="extraPersons">Number of Extra Persons:</label>
    <input type="number" id="extraPersons" name="Extra Persons" min="1" value="1">
  </div>
</div>

<!-- Total Price -->
<div class="price-display">
  Total Price: ₹ <span id="totalPrice">0.00</span>
  <input type="hidden" id="totalPriceInput" name="Total Price" value="0.00">
</div>

<!-- Pay & Submit Button -->
<button type="submit" class="razorpay-button">Book Now</button>

  </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>const rates = {
    Single: { CP: 2500, MAP: 3190 },
    Double: { CP: 3500, MAP: 4290 },
    Extra: { CP: 1200, MAP: 0 }
  };

  const occupancySelect = document.getElementById('occupancy');
  const packageTypeSelect = document.getElementById('packageType');
  const nightsInput = document.getElementById('nights');
  const checkinInput = document.getElementById('checkin');
  const checkoutInput = document.getElementById('checkout');
  const addExtraPersonCheckbox = document.getElementById('addExtraPerson');
  const extraPersonGroup = document.getElementById('extraPersonGroup');
  const extraPersonsInput = document.getElementById('extraPersons');
  const totalPriceSpan = document.getElementById('totalPrice');
  const totalPriceInput = document.getElementById('totalPriceInput');
  const payButton = document.getElementById('payButton');
  const bookingForm = document.getElementById('bookingForm');

  function updateExtraPersonVisibility() {
    if (occupancySelect.value === 'Single') {
      document.getElementById('extraPersonOptions').style.display = 'none';
      addExtraPersonCheckbox.checked = false;
      extraPersonsInput.value = 1;
    } else {
      document.getElementById('extraPersonOptions').style.display = 'block';
    }
  }

  function calculateNights() {
    const checkin = new Date(checkinInput.value);
    const checkout = new Date(checkoutInput.value);
    if (checkin && checkout && checkout > checkin) {
      const diffTime = checkout - checkin;
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      nightsInput.value = diffDays;
    } else {
      nightsInput.value = 1;
    }
  }

  function calculateTotalPrice() {
    const occupancy = occupancySelect.value;
    const packageType = packageTypeSelect.value;
    const nights = parseInt(nightsInput.value) || 1;
    const addExtra = addExtraPersonCheckbox.checked && occupancy !== 'Single';
    const extraPersons = addExtra ? (parseInt(extraPersonsInput.value) || 1) : 0;

    let baseRate = rates[occupancy][packageType];
    let extraRate = addExtra ? rates.Extra[packageType] * extraPersons : 0;

    const total = (baseRate + extraRate) * nights;
    totalPriceSpan.textContent = total.toFixed(2);
    totalPriceInput.value = total.toFixed(2);
    return total;
  }

  occupancySelect.addEventListener('change', () => { updateExtraPersonVisibility(); calculateTotalPrice(); });
  packageTypeSelect.addEventListener('change', calculateTotalPrice);
  nightsInput.addEventListener('input', calculateTotalPrice);
  addExtraPersonCheckbox.addEventListener('change', () => {
    extraPersonGroup.style.display = addExtraPersonCheckbox.checked ? 'block' : 'none';
    calculateTotalPrice();
  });
  extraPersonsInput.addEventListener('input', calculateTotalPrice);
  checkinInput.addEventListener('change', () => { calculateNights(); calculateTotalPrice(); });
  checkoutInput.addEventListener('change', () => { calculateNights(); calculateTotalPrice(); });

  // Initial
  updateExtraPersonVisibility();
  calculateTotalPrice();
  const propertyId = "<?= htmlspecialchars($property_id) ?>";

// Fetch booked dates from the server
fetch(`get_booked_dates.php?property_id=${propertyId}`)
  .then(response => response.json())
  .then(bookedDates => {
    // Initialize Flatpickr for check-in and check-out fields
    flatpickr("#checkin", {
      minDate: "today", // Disable past dates
      disable: bookedDates, // Disable booked dates
      dateFormat: "Y-m-d"
    });

    flatpickr("#checkout", {
      minDate: "today",
      disable: bookedDates, // Disable booked dates
      dateFormat: "Y-m-d"
    });
  })
  .catch(error => console.error('Error fetching booked dates:', error));

  </script>

</body>

</html>

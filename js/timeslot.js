 // date logic 
const startDate = document.getElementById('start_date');
const endDate   = document.getElementById('end_date');
const toText    = document.getElementById('toText');
const timeGroup = document.querySelector('.time-group');
const bookingRadios = document.querySelectorAll('input[name="booking_type"]');

// Today (YYYY-MM-DD)
const today = new Date().toISOString().split('T')[0];

// Default date
startDate.value = today;
startDate.min   = today;
endDate.min     = today;

// Ensure end date >= start date
startDate.addEventListener('change', () => {
    endDate.min = startDate.value;
    if (endDate.value < startDate.value) {
        endDate.value = startDate.value;
    }
});

// Toggle Booking Type
function toggleBookingUI() {
    const selected = document.querySelector('input[name="booking_type"]:checked').value;

    if (selected === 'hourly') {
        // Hourly → single date only
        timeGroup.style.display = 'flex';
        endDate.style.display = 'none';
        toText.style.display  = 'none';
        endDate.required = false;

        // Set end date same as start date
        endDate.value = startDate.value;

    } else {
        // Full-day → date range
        timeGroup.style.display = 'none';
        endDate.style.display = 'inline-block';
        toText.style.display  = 'inline';
        endDate.required = true;
    }
}

// Initial state
toggleBookingUI();
bookingRadios.forEach(r => r.addEventListener("change", toggleBookingUI));

document.getElementById("form").addEventListener("submit", function(e){

    // vehicle validation
    const v = document.querySelector("[name='vehicle']").value.trim();
    if (!/^[A-Z]{2}[0-9]{1,2}[A-Z]{1,2}[0-9]{4}$/.test(v)) {
        alert("Invalid vehicle number");
        e.preventDefault();
        return;
    }

    // time validation (hourly only)
    if (document.querySelector('input[name="booking_type"]:checked').value !== 'hourly') {
        return;
    }
const sh = parseInt(document.querySelector("[name='start_hour']").value);
const eh = parseInt(document.querySelector("[name='end_hour']").value);
const sa = document.querySelector("[name='start_ampm']").value;
const ea = document.querySelector("[name='end_ampm']").value;

function to24(h,a){
if(a==="PM" && h!==12) h+=12;
if(a==="AM" && h===12) h=0;
return h;
}

if (to24(eh,ea) <= to24(sh,sa)) {
e.preventDefault();
const err = document.getElementById("timeError");
err.innerText = "End time must be greater than start time";
err.style.display = "block";
}
});
// Uppercase vehicle
document.querySelector("[name='vehicle']").addEventListener("input", function(){
    this.value = this.value.toUpperCase();
});
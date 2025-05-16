<div class="mt-4">
  <h1 class="text-center" style="text-decoration: underline wavy blue 2px; text-underline-offset: 4px;">Calendly Schedule Extractor</h1>
    <form action="/" method="post" class="mt-4">
      <div class="d-flex flex-column align-items-center">
        <div class="">
          <label for="calendly_url" class="form-label" style="font-weight: bold;">Calendly Link</label>
          <input style="width: 400px;" type="url" class="form-control" id="calendly_url" name="calendly_url" required placeholder="https://calendly.com/username/event">
        <div>
      </div>

      <div class="mt-4 d-flex justify-content-center">
        <button type="submit" class="btn btn-primary">Extract Availability</button>
      </div>      
  </form>
</div>

 <?php if (isset($avail_data) && is_array($avail_data)): ?>

  <div class="container my-5">
    <h2 class="mb-4">Availability (Next 4 Weeks)</h2>
    <div id="availability"></div>
  </div>

  <script>
    const data = <?= json_encode($avail_data); ?>;

    const container = document.getElementById('availability');

    Object.keys(data).forEach(year => {
      const months = data[year];

      Object.keys(months).forEach(month => {
        const daySlots = months[month];

        const monthSection = document.createElement('div');
        monthSection.className = 'mb-5';

        const heading = document.createElement('h3');
        heading.className = 'mb-3';
        heading.textContent = month + " " + year;
        monthSection.appendChild(heading);

        const row = document.createElement('div');
        row.className = 'row g-3';

        daySlots.forEach(dayObj => {
          const col = document.createElement('div');
          col.className = 'col-md-4 col-sm-6';

          const card = document.createElement('div');
          card.className = 'card shadow-sm h-100';

          const cardBody = document.createElement('div');
          cardBody.className = 'card-body';

          const dayTitle = document.createElement('h5');
          dayTitle.className = 'card-title';
          dayTitle.textContent = `Day ${dayObj.day}`;

          const timeContainer = document.createElement('div');
          if (dayObj.timeSlots && dayObj.timeSlots.length > 0) {
            dayObj.timeSlots.forEach(slot => {
              const slotEl = document.createElement('span');
              slotEl.className = 'time-slot';
              slotEl.textContent = slot;
              timeContainer.appendChild(slotEl);
            });
          } else {
            const noSlot = document.createElement('div');
            noSlot.className = 'no-slots';
            noSlot.textContent = 'No time slots';
            timeContainer.appendChild(noSlot);
          }

          cardBody.appendChild(dayTitle);
          cardBody.appendChild(timeContainer);
          card.appendChild(cardBody);
          col.appendChild(card);
          row.appendChild(col);
        });

        monthSection.appendChild(row);
        container.appendChild(monthSection);
      });
    });
    
  </script>
<?php endif; ?>
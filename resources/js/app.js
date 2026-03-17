import './bootstrap';

const input = document.getElementById("addressSearch");

input.addEventListener("keypress", async function(e) {

  if (e.key === "Enter") {

    e.preventDefault();

    const query = input.value;

    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`
    );

    const data = await response.json();

    if (data.length > 0) {

      const lat = data[0].lat;
      const lon = data[0].lon;

      map.setView([lat, lon], 16);

      if (window.marker) {
        map.removeLayer(marker);
      }

      marker = L.marker([lat, lon]).addTo(map);

      document.getElementById("lat").value = lat;
      document.getElementById("lon").value = lon;

    } else {
      alert("Adresse non trouvée");
    }
  }

});



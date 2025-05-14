jQuery(document).ready(function ($) {
  // Seleccionar todas las secciones de temporada
  const temporadaSections = document.querySelectorAll(".temporada-section");

  temporadaSections.forEach((section) => {
    // Seleccionar el contenedor de tarjetas
    const cardsContainer = section.querySelector(
      ".row.row-cols-1.row-cols-md-2.row-cols-lg-3.g-5.space-gap"
    );
    if (!cardsContainer) return;

    // Obtener todas las tarjetas de obra
    const cards = Array.from(cardsContainer.querySelectorAll(".obra-card"));
    if (cards.length < 2) return;

    // Mapeo de meses en español e inglés
    const monthMap = {
      Ene: 0,
      Jan: 0,
      Feb: 1,
      Mar: 2,
      Abr: 3,
      Apr: 3,
      May: 4,
      Jun: 5,
      Jul: 6,
      Ago: 7,
      Aug: 7,
      Sep: 8,
      Oct: 9,
      Nov: 10,
      Dic: 11,
      Dec: 11,
    };

    // Función para extraer la fecha más temprana
    const getEarliestDate = (card) => {
      const datesElement = card.querySelector(".card-dates");
      if (!datesElement) return new Date(9999, 11, 31); // Fecha muy futura

      // Obtener solo el texto (ignorando elementos hijos como el icono)
      let dateText = "";
      datesElement.childNodes.forEach((node) => {
        if (node.nodeType === Node.TEXT_NODE) {
          dateText += node.textContent;
        }
      });

      // Extraer todas las fechas en formato "d MMM YYYY"
      const dateMatches = dateText.match(/\d{1,2} [A-Za-z]{3} \d{4}/g) || [];
      if (dateMatches.length === 0) return new Date(9999, 11, 31);

      // Convertir a objetos Date y obtener la más temprana
      const dates = dateMatches.map((dateStr) => {
        const [day, month, year] = dateStr.split(" ");
        return new Date(year, monthMap[month], day);
      });

      return new Date(Math.min(...dates.map((d) => d.getTime())));
    };

    // Ordenar las tarjetas por fecha más temprana
    cards.sort((a, b) => {
      const dateA = getEarliestDate(a);
      const dateB = getEarliestDate(b);
      return dateA - dateB;
    });

    // Reorganizar las tarjetas en el DOM
    cards.forEach((card) => {
      cardsContainer.appendChild(card);
    });

    console.log(
      `Temporada ordenada: "${section.querySelector("h2").textContent.trim()}"`
    );
  });
});

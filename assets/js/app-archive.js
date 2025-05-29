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

    // Función para extraer la última fecha del rango (fecha de finalización)
    const getLatestDate = (card) => {
      const datesElement = card.querySelector(".card-dates");
      if (!datesElement) return null;

      // Obtener solo el texto (ignorando elementos hijos como el icono)
      let dateText = "";
      datesElement.childNodes.forEach((node) => {
        if (node.nodeType === Node.TEXT_NODE) dateText += node.textContent;
      });

      // Extraer todas las fechas en formato "d MMM YYYY"
      const dateMatches = dateText.match(/\d{1,2} [A-Za-z]{3} \d{4}/g) || [];
      if (dateMatches.length === 0) return null;

      // Convertir a objetos Date y obtener la última (fecha de finalización)
      const dates = dateMatches.map((dateStr) => {
        const [day, month, year] = dateStr.split(" ");
        return new Date(year, monthMap[month], day);
      });

      return dates[dates.length - 1]; // Retorna la última fecha del rango
    };

    // Función para obtener el número de orden de publicación (si existe)
    const getPublicationOrder = (card) => {
      const orderElement = card.querySelector(".orden-publicacion");
      if (!orderElement) return null;
      const orderText = orderElement.textContent.trim();
      return orderText ? parseInt(orderText, 10) : null;
    };

    // Separar las cards en dos grupos:
    const cardsWithOrder = cards.filter(
      (card) => getPublicationOrder(card) !== null
    );
    const cardsWithDates = cards.filter((card) => getLatestDate(card) !== null);

    // **Caso 1: Solo hay cards con orden numérico → Ordenar por número**
    if (cardsWithOrder.length > 0 && cardsWithDates.length === 0) {
      cards.sort((a, b) => getPublicationOrder(a) - getPublicationOrder(b));
    }
    // **Caso 2: Solo hay cards con fechas → Ordenar por fecha de finalización**
    else if (cardsWithOrder.length === 0 && cardsWithDates.length > 0) {
      cards.sort((a, b) => getLatestDate(a) - getLatestDate(b));
    }
    // **Caso 3: Hay cards mixtas → Ordenar primero por número, luego por fecha**
    else if (cardsWithOrder.length > 0 && cardsWithDates.length > 0) {
      // Ordenar las que tienen número
      const sortedByOrder = cardsWithOrder.sort(
        (a, b) => getPublicationOrder(a) - getPublicationOrder(b)
      );

      // Ordenar las que tienen fecha (y no tienen número)
      const cardsWithoutOrder = cards.filter(
        (card) => getPublicationOrder(card) === null
      );
      const sortedByDate = cardsWithoutOrder.sort(
        (a, b) => getLatestDate(a) - getLatestDate(b)
      );

      // Combinar los dos grupos (primero las ordenadas por número, luego por fecha)
      cards.length = 0; // Limpiar el array original
      cards.push(...sortedByOrder, ...sortedByDate); // Reconstruir en el orden correcto
    }

    // Reorganizar las tarjetas en el DOM
    cards.forEach((card) => {
      cardsContainer.appendChild(card);
    });

    console.log(
      `Temporada ordenada: "${section.querySelector("h2").textContent.trim()}"`
    );
  });
});

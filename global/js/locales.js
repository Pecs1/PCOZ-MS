// The locale our app first shows
const defaultLocale = "en";

// The active locale
let locale;

// Gets filled with active locale translations
let translations = {};

// When the page content is ready...
document.addEventListener("DOMContentLoaded", () => {
  // Try to load the locale from localStorage, otherwise use the default
  const savedLocale = localStorage.getItem("preferredLocale");
  setLocale(savedLocale || defaultLocale);

  // Add event listener to the locale switcher
  // Selector must match your HTML: '[data-locale-switcher]'
  const localeSwitcher = document.querySelector("[data-locale-switcher]");
  if (localeSwitcher) {
    // Update the select box to show the saved/default locale initially
    localeSwitcher.value = savedLocale || defaultLocale;

    localeSwitcher.addEventListener("change", (event) => {
      const newLocale = event.target.value;
      setLocale(newLocale);
      localStorage.setItem("preferredLocale", newLocale); // Save the new preferred locale to localStorage
      location.reload(); // Reload the page to apply PHP-side translation changes
    });
  }
});

// Load translations for the given locale and translate
// the page to this locale
async function setLocale(newLocale) {
  if (newLocale === locale) {
    return;
  }

  const newTranslations = await fetchTranslationsFor(newLocale);
  locale = newLocale;
  translations = newTranslations;
  translatePage(); // This will now call renderFaqs() internally

  // If setLocale is called programmatically (e.g., on initial load),
  // this ensures the dropdown visually reflects the active locale.
  // Selector must match your HTML: '[data-locale-switcher]'
  const localeSwitcher = document.querySelector("[data-locale-switcher]");
  if (localeSwitcher && localeSwitcher.value !== newLocale) {
    localeSwitcher.value = newLocale;
  }
}

// Retrieve translations JSON object for the given
// locale over the network
async function fetchTranslationsFor(newLocale) {
  const response = await fetch(`/global/lang/${newLocale}.json`);
  if (!response.ok) { // Basic error checking for network requests
      console.error(`Failed to fetch ${newLocale}.json: ${response.status} ${response.statusText}`);
      return {}; // Return empty object to prevent errors down the line
  }
  return await response.json();
}

// Replace the inner text or specific attributes of each element that has a
// data-locale-key attribute with the translation corresponding
// to its data-locale-key
function translatePage() {
  // Selects elements with data-locale-key for static text/attributes
  document
    .querySelectorAll("[data-locale-key]") // Selector must match your HTML: '[data-locale-key]'
    .forEach(translateElement);

  // IMPORTANT: Call renderFaqs() here after static elements are translated
  renderFaqs();
}

// Replace the inner text or specific attributes of the given HTML element
// with the translation in the active locale,
// corresponding to the element's data-locale-key
function translateElement(element) {
  // Attribute name must match your HTML: "data-locale-key"
  const key = element.getAttribute("data-locale-key");

  if (key) {
    if (key.includes('.')) {
      // Handles keys like "search-input.placeholder" for attributes
      const [baseKey, attributeName] = key.split('.');
      const translationObject = translations[baseKey];
      if (translationObject && translationObject[attributeName]) {
        const attributeValue = translationObject[attributeName];
        element.setAttribute(attributeName, attributeValue);
      } else {
        console.warn(`Translation for attribute "${attributeName}" not found under base key "${baseKey}" for element:`, element);
      }
    } else {
      // Handles direct keys like "app-title" or "home" for innerText
      const translation = translations[key];
      if (translation) {
        element.innerText = translation;
      } else {
        console.warn(`Translation not found for key "${key}" for element:`, element);
      }
    }
  } else {
    console.warn("Element is missing 'data-locale-key' attribute:", element);
  }
}

// NEW FUNCTION: Define renderFaqs() here
// This function dynamically generates the FAQ list using <details> and <summary>
function renderFaqs() {
  const faqListContainer = document.getElementById("faq-list");
  if (!faqListContainer) {
    console.warn("FAQ list container (id='faq-list') not found in HTML.");
    return;
  }

  // Clear existing FAQs to avoid duplicates when switching languages
  faqListContainer.innerHTML = '';

  const faqQuestions = translations["faq-questions"]; // Get the array of FAQ objects from the loaded translations
  if (faqQuestions && Array.isArray(faqQuestions)) {
    faqQuestions.forEach(faqItem => {
      // Create <details> and <summary> elements for each FAQ item
      const detailsElement = document.createElement('details');
      detailsElement.className = 'faq-item'; // Add a class for styling (e.g., for CSS)

      const summaryElement = document.createElement('summary');
      summaryElement.className = 'faq-question-summary'; // Add class for summary styling
      summaryElement.innerText = faqItem.question; // Sets the question text

      const answerElement = document.createElement('p');
      answerElement.className = 'faq-answer'; // Add class for answer styling

      let answerContent = faqItem.answer; // Start with the base answer text

      // Check if link data exists and process it
      if (faqItem['answer-link-text'] && faqItem['answer-link-url']) {
          const linkText = faqItem['answer-link-text'];
          const linkUrl = faqItem['answer-link-url'];

          // Create the actual HTML <a> tag
          const linkHtml = `<a href="${linkUrl}" target="_blank">${linkText}</a>`;

          // Replace the specified linkText within the answerContent with the generated HTML link
          // This assumes linkText exists exactly as-is in answerContent.
          // For more robust replacement (e.g., case-insensitive, multiple links),
          // you'd need more complex regex or a Markdown parser.
          answerContent = answerContent.replace(linkText, linkHtml);
      }

      answerElement.innerHTML = answerContent; // Use innerHTML to parse the generated link HTML

      detailsElement.appendChild(summaryElement);
      detailsElement.appendChild(answerElement);
      faqListContainer.appendChild(detailsElement);
    });
  } else {
    console.warn("FAQ questions data not found or not in expected format in translations.");
  }
}
import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import hu from './locales/hu.json';
import de from './locales/de.json';

const resources = {
  hu: { translation: hu },
  de: { translation: de },
};

i18n.use(initReactI18next).init({
  resources,
  lng: 'hu',
  fallbackLng: 'hu',
  interpolation: {
    escapeValue: false,
  },
});

export default i18n;

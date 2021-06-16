import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {MicroFrontendDependenciesProvider, Routes, Translations} from '@akeneo-pim-community/shared';
import {routes} from './routes.json';
import translations from './translations.json';
import {ConfigContext, UnsavedChangesContext} from './feature';
import MeasurementApp from './feature/MeasurementApp';

const unsavedChanges = {
  hasUnsavedChanges: false,
  setHasUnsavedChanges: (hasChanges: boolean) => (unsavedChanges.hasUnsavedChanges = hasChanges),
};

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={pimTheme}>
      <MicroFrontendDependenciesProvider routes={routes as Routes} translations={translations as Translations}>
        <ConfigContext.Provider value={{families_max: 10, operations_max: 10, units_max: 10}}>
          <UnsavedChangesContext.Provider value={unsavedChanges}>
            <MeasurementApp />
          </UnsavedChangesContext.Provider>
        </ConfigContext.Provider>
      </MicroFrontendDependenciesProvider>
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById('root')
);

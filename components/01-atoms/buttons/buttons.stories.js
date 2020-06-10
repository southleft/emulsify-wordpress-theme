import React from 'react';

import button from './button.twig';

import buttonData from './button.yml';
import buttonAltData from './button-alt.yml';
import buttonAlt2Data from './button-alt2.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Atoms/Button' };

export const twig = () => (
  <div dangerouslySetInnerHTML={{ __html: button(buttonData) }} />
);
export const twigAlt = () => (
  <div dangerouslySetInnerHTML={{ __html: button(buttonAltData) }} />
);
export const twigAlt2 = () => (
  <div dangerouslySetInnerHTML={{ __html: button(buttonAlt2Data) }} />
);

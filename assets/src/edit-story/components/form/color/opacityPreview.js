/*
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * External dependencies
 */
import { useState, useCallback, useEffect } from 'react';
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { _x, __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { PatternPropType } from '../../../types';
import getPreviewText from './getPreviewText';
import getPreviewOpacity from './getPreviewOpacity';
import ColorBox from './colorBox';

const Input = styled(ColorBox).attrs({
  as: 'input',
})`
  border: 0;
  margin-left: 6px;
  width: 54px;
  line-height: 32px;
  text-align: center;
  visibility: ${({ isVisible }) => (isVisible ? 'visible' : 'hidden')};
`;

function OpacityPreview({ value, onChange }) {
  const hasPreviewText = Boolean(getPreviewText(value));
  const postfix = _x('%', 'Percentage', 'web-stories');
  const [inputValue, setInputValue] = useState('');
  const [hasPostfix, setHasPostfix] = useState(true);
  const enablePostfix = useCallback(() => setHasPostfix(true), []);
  const disablePostfix = useCallback(() => setHasPostfix(false), []);

  // Allow any input, but only persist non-NaN values up-chain
  const handleChange = useCallback(
    (evt) => {
      setInputValue(evt.target.value);
      const val = parseInt(evt.target.value) / 100;
      if (!isNaN(val)) {
        onChange(val);
      }
    },
    [onChange]
  );

  const updateFromValue = useCallback(
    () => setInputValue(getPreviewOpacity(value)),
    [value]
  );

  // However on blur, restore to last persisted value
  const handleBlur = useCallback(() => {
    enablePostfix();
    updateFromValue();
  }, [enablePostfix, updateFromValue]);

  useEffect(() => updateFromValue(), [updateFromValue, value]);

  return (
    <Input
      type="text"
      aria-label={__('Opacity', 'web-stories')}
      isVisible={hasPreviewText}
      onBlur={handleBlur}
      onFocus={disablePostfix}
      onChange={handleChange}
      value={hasPostfix ? `${inputValue}${postfix}` : inputValue}
    />
  );
}

OpacityPreview.propTypes = {
  value: PatternPropType,
  onChange: PropTypes.func.isRequired,
};

export default OpacityPreview;

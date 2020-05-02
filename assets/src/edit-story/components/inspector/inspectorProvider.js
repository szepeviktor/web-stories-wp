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
import PropTypes from 'prop-types';
import { useCallback, useState, useRef, useEffect } from 'react';

/**
 * Internal dependencies
 */
import { useAPI } from '../../app/api';
import useResizeEffect from '../../utils/useResizeEffect';
import { useStory } from '../../app/story';
import Context from './context';

const DESIGN = 'design';
const DOCUMENT = 'document';
const PREPUBLISH = 'prepublish';

function InspectorProvider({ children }) {
  const {
    actions: { getAllStatuses, getAllUsers },
  } = useAPI();
  const {
    state: { selectedElementIds, currentPage },
  } = useStory();

  const [tab, setTab] = useState(DESIGN);
  const [users, setUsers] = useState([]);
  const [statuses, setStatuses] = useState([]);
  const [inspectorContentHeight, setInspectorContentHeight] = useState(null);
  const inspectorContentRef = useRef();
  const tabRef = useRef(tab);

  const [isUsersLoading, setIsUsersLoading] = useState(false);
  const [isStatusesLoading, setIsStatusesLoading] = useState(false);

  const setInspectorContentNode = useCallback((node) => {
    inspectorContentRef.current = node;
  }, []);

  useResizeEffect(inspectorContentRef, ({ height }) =>
    setInspectorContentHeight(height)
  );

  useEffect(() => {
    tabRef.current = tab;
  }, [tab]);

  useEffect(() => {
    if (selectedElementIds.length > 0 && tabRef.current === DOCUMENT) {
      setTab(DESIGN);
    }
  }, [selectedElementIds]);

  useEffect(() => {
    if (tabRef.current === DOCUMENT) {
      setTab(DESIGN);
    }
  }, [currentPage]);

  const loadStatuses = useCallback(() => {
    if (!isStatusesLoading && statuses.length === 0) {
      setIsStatusesLoading(true);
      getAllStatuses()
        .then((data) => {
          data = Object.values(data);
          data = data.filter(({ show_in_list: isShown }) => isShown);
          const saveData = data.map(({ slug, name }) => ({
            value: slug,
            name,
          }));
          setStatuses(saveData);
        })
        .finally(() => {
          setIsStatusesLoading(false);
        });
    }
  }, [isStatusesLoading, statuses.length, getAllStatuses]);

  const loadUsers = useCallback(() => {
    if (!isUsersLoading && users.length === 0) {
      setIsUsersLoading(true);
      getAllUsers()
        .then((data) => {
          const saveData = data.map(({ id, name }) => ({
            value: id,
            name,
          }));

          setUsers(saveData);
        })
        .finally(() => {
          setIsUsersLoading(false);
        });
    }
  }, [isUsersLoading, users.length, getAllUsers]);

  const state = {
    state: {
      tab,
      users,
      statuses,
      inspectorContentHeight,
      isUsersLoading,
    },
    actions: {
      setTab,
      loadStatuses,
      loadUsers,
      setInspectorContentNode,
    },
    data: {
      tabs: {
        DESIGN,
        DOCUMENT,
        PREPUBLISH,
      },
    },
  };

  return <Context.Provider value={state}>{children}</Context.Provider>;
}

InspectorProvider.propTypes = {
  children: PropTypes.node,
};

export default InspectorProvider;
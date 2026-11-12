document.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('tma-app-root');
  if (!root || typeof tmaAppData === 'undefined') {
    return;
  }

  const state = {
    workspaces: [],
    tasks: [],
    activity: [],
    selectedWorkspaceId: null,
    taskRequest: 0,
    activityRequest: 0,
    confirmResolver: null,
  };

  const element = (tag, className, text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  };

  const control = (labelText, field) => {
    const label = element('label', 'tma-field');
    const caption = element('span', 'tma-field-label', labelText);
    label.append(caption, field);
    return label;
  };

  const createInput = (id, type, className) => {
    const input = element('input', className);
    input.id = id;
    input.type = type;
    return input;
  };

  const createSelect = (id, className, options) => {
    const select = element('select', className);
    select.id = id;
    options.forEach(([value, label]) => {
      const option = element('option', '', label);
      option.value = value;
      select.append(option);
    });
    return select;
  };

  const buildLayout = () => {
    root.replaceChildren();

    const shell = element('div', 'tma-shell');
    const sidebar = element('aside', 'tma-sidebar');
    sidebar.setAttribute('aria-label', 'Workspaces');
    const workspaceHeading = element('div', 'tma-section-heading');
    workspaceHeading.append(
      element('p', 'tma-eyebrow', 'YOUR PROJECTS'),
      element('h2', '', 'Workspaces')
    );
    const workspaceList = element('ul', 'tma-workspace-list');
    workspaceList.id = 'tma-workspace-list';

    const workspaceForm = element('form', 'tma-workspace-form');
    workspaceForm.id = 'tma-workspace-form';
    const workspaceInput = createInput('tma-workspace-name', 'text', 'tma-input');
    workspaceInput.placeholder = 'e.g. Marketing launch';
    workspaceInput.maxLength = 255;
    workspaceInput.required = true;
    workspaceForm.append(
      control('New workspace', workspaceInput),
      Object.assign(element('button', 'tma-button tma-button--quiet', 'Add workspace'), { type: 'submit' })
    );
    sidebar.append(workspaceHeading, workspaceList, workspaceForm);

    const main = element('section', 'tma-main-panel');
    main.setAttribute('aria-label', 'Task workspace');
    const header = element('header', 'tma-page-header');
    const titleBlock = element('div', 'tma-section-heading');
    titleBlock.append(element('p', 'tma-eyebrow', 'PLAN WITH CLARITY'), element('h1', '', 'Tasks'));
    const newTaskButton = element('button', 'tma-button', 'New task');
    newTaskButton.id = 'tma-new-task';
    newTaskButton.type = 'button';
    header.append(titleBlock, newTaskButton);

    const toolbar = element('div', 'tma-toolbar');
    const search = createInput('tma-task-search', 'search', 'tma-input');
    search.placeholder = 'Search tasks';
    search.setAttribute('aria-label', 'Search tasks');
    const status = createSelect('tma-task-status-filter', 'tma-select', [
      ['', 'All statuses'],
      ['todo', 'To do'],
      ['in-progress', 'In progress'],
      ['done', 'Done'],
    ]);
    status.setAttribute('aria-label', 'Filter by status');
    const priority = createSelect('tma-task-priority-filter', 'tma-select', [
      ['', 'All priorities'],
      ['low', 'Low priority'],
      ['medium', 'Medium priority'],
      ['high', 'High priority'],
    ]);
    priority.setAttribute('aria-label', 'Filter by priority');
    const sort = createSelect('tma-task-sort', 'tma-select', [
      ['due_date', 'Due date'],
      ['created_at', 'Date created'],
      ['title', 'Title'],
      ['priority', 'Priority'],
      ['status', 'Status'],
    ]);
    sort.setAttribute('aria-label', 'Sort tasks by');
    const order = createSelect('tma-task-order', 'tma-select', [
      ['ASC', 'Ascending'],
      ['DESC', 'Descending'],
    ]);
    order.setAttribute('aria-label', 'Sort direction');
    const refresh = element('button', 'tma-button tma-button--quiet', 'Refresh');
    refresh.id = 'tma-refresh';
    refresh.type = 'button';
    toolbar.append(search, status, priority, sort, order, refresh);

    const statusMessage = element('p', 'tma-status-message');
    statusMessage.id = 'tma-status-message';
    statusMessage.setAttribute('role', 'status');
    statusMessage.setAttribute('aria-live', 'polite');
    const taskList = element('ul', 'tma-task-list');
    taskList.id = 'tma-task-list';

    const activitySection = element('section', 'tma-activity-section');
    activitySection.setAttribute('aria-labelledby', 'tma-activity-heading');
    const activityHeading = element('h2', '', 'Recent activity');
    activityHeading.id = 'tma-activity-heading';
    const activityList = element('ul', 'tma-activity-list');
    activityList.id = 'tma-activity-list';
    activitySection.append(activityHeading, activityList);

    const dialog = element('dialog', 'tma-dialog');
    dialog.id = 'tma-task-dialog';
    dialog.setAttribute('aria-labelledby', 'tma-dialog-heading');
    const taskForm = element('form', 'tma-task-form');
    taskForm.id = 'tma-task-form';
    const dialogHeader = element('div', 'tma-dialog-header');
    const dialogTitle = element('h2', '', 'New task');
    dialogTitle.id = 'tma-dialog-heading';
    const dialogIntro = element('p', '', 'Add details and keep your next step clear.');
    dialogHeader.append(dialogTitle, dialogIntro);
    const taskTitle = createInput('tma-task-title', 'text', 'tma-input');
    taskTitle.maxLength = 255;
    taskTitle.required = true;
    const taskDescription = element('textarea', 'tma-textarea');
    taskDescription.id = 'tma-task-description';
    taskDescription.rows = 4;
    const taskPriority = createSelect('tma-task-priority', 'tma-select', [
      ['low', 'Low'],
      ['medium', 'Medium'],
      ['high', 'High'],
    ]);
    taskPriority.id = 'tma-task-priority';
    taskPriority.value = 'medium';
    const taskStatus = createSelect('tma-task-status', 'tma-select', [
      ['todo', 'To do'],
      ['in-progress', 'In progress'],
      ['done', 'Done'],
    ]);
    taskStatus.id = 'tma-task-status';
    const dueDate = createInput('tma-task-due-date', 'date', 'tma-input');
    const fields = element('div', 'tma-form-grid');
    fields.append(
      control('Task title', taskTitle),
      control('Description', taskDescription),
      control('Priority', taskPriority),
      control('Status', taskStatus),
      control('Due date', dueDate)
    );
    const formActions = element('div', 'tma-dialog-actions');
    const cancel = element('button', 'tma-button tma-button--quiet', 'Cancel');
    cancel.type = 'button';
    cancel.dataset.action = 'cancel-task';
    const save = element('button', 'tma-button', 'Save task');
    save.type = 'submit';
    formActions.append(cancel, save);
    taskForm.append(dialogHeader, fields, formActions);
    dialog.append(taskForm);

    const workspaceDialog = element('dialog', 'tma-dialog tma-dialog--compact');
    workspaceDialog.id = 'tma-workspace-dialog';
    workspaceDialog.setAttribute('aria-labelledby', 'tma-workspace-dialog-heading');
    const workspaceEditForm = element('form', 'tma-task-form');
    workspaceEditForm.id = 'tma-workspace-edit-form';
    const workspaceEditHeading = element('h2', '', 'Rename workspace');
    workspaceEditHeading.id = 'tma-workspace-dialog-heading';
    const workspaceEditInput = createInput('tma-workspace-edit-name', 'text', 'tma-input');
    workspaceEditInput.maxLength = 255;
    workspaceEditInput.required = true;
    const workspaceEditActions = element('div', 'tma-dialog-actions');
    const workspaceEditCancel = element('button', 'tma-button tma-button--quiet', 'Cancel');
    workspaceEditCancel.type = 'button';
    workspaceEditCancel.dataset.action = 'cancel-workspace';
    const workspaceEditSave = element('button', 'tma-button', 'Save workspace');
    workspaceEditSave.type = 'submit';
    workspaceEditActions.append(workspaceEditCancel, workspaceEditSave);
    workspaceEditForm.append(workspaceEditHeading, control('Workspace name', workspaceEditInput), workspaceEditActions);
    workspaceDialog.append(workspaceEditForm);

    const confirmDialog = element('dialog', 'tma-dialog tma-dialog--compact');
    confirmDialog.id = 'tma-confirm-dialog';
    confirmDialog.setAttribute('aria-labelledby', 'tma-confirm-heading');
    const confirmContent = element('div', 'tma-task-form');
    const confirmHeading = element('h2', '', 'Please confirm');
    confirmHeading.id = 'tma-confirm-heading';
    const confirmMessage = element('p', 'tma-confirm-message');
    confirmMessage.id = 'tma-confirm-message';
    const confirmActions = element('div', 'tma-dialog-actions');
    const confirmCancel = element('button', 'tma-button tma-button--quiet', 'Cancel');
    confirmCancel.type = 'button';
    confirmCancel.dataset.action = 'cancel-confirmation';
    const confirmAccept = element('button', 'tma-button tma-button--danger', 'Delete');
    confirmAccept.type = 'button';
    confirmAccept.dataset.action = 'accept-confirmation';
    confirmActions.append(confirmCancel, confirmAccept);
    confirmContent.append(confirmHeading, confirmMessage, confirmActions);
    confirmDialog.append(confirmContent);

    main.append(header, toolbar, statusMessage, taskList, activitySection);
    shell.append(sidebar, main);
    root.append(shell, dialog, workspaceDialog, confirmDialog);

    workspaceForm.addEventListener('submit', createWorkspace);
    taskForm.addEventListener('submit', saveTask);
    workspaceEditForm.addEventListener('submit', saveWorkspace);
    confirmDialog.addEventListener('close', () => {
      const resolve = state.confirmResolver;
      state.confirmResolver = null;
      if (resolve) resolve(confirmDialog.returnValue === 'confirm');
    });
    search.addEventListener('input', debounce(loadTasks, 250));
    [status, priority, sort, order].forEach((input) => input.addEventListener('change', loadTasks));
    refresh.addEventListener('click', loadWorkspaces);
    newTaskButton.addEventListener('click', () => openTaskDialog());
    root.addEventListener('click', handleClick);
  };

  const setStatus = (message, isError = false) => {
    const target = root.querySelector('#tma-status-message');
    if (!target) return;
    target.textContent = message;
    target.classList.toggle('is-error', isError);
  };

  const request = async (path, options = {}) => {
    const response = await fetch(`${tmaAppData.restUrl}${path}`, {
      credentials: 'same-origin',
      ...options,
      headers: {
        'X-WP-Nonce': tmaAppData.nonce,
        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
        ...options.headers,
      },
    });

    let payload;
    try {
      payload = await response.json();
    } catch (error) {
      throw new Error('The server returned an unreadable response.');
    }

    if (!response.ok || payload.success === false) {
      throw new Error(payload.message || payload.message?.message || 'The request could not be completed.');
    }
    return payload;
  };

  const handleError = (error) => {
    setStatus(error.message || 'Something went wrong. Please try again.', true);
  };

  const renderWorkspaceList = () => {
    const list = root.querySelector('#tma-workspace-list');
    list.replaceChildren();

    state.workspaces.forEach((workspace) => {
      const item = element('li', 'tma-workspace-item');
      const select = element('button', 'tma-workspace-select', workspace.name);
      select.type = 'button';
      select.dataset.action = 'select-workspace';
      select.dataset.id = workspace.id;
      select.setAttribute('aria-pressed', String(Number(workspace.id) === state.selectedWorkspaceId));
      if (Number(workspace.id) === state.selectedWorkspaceId) select.classList.add('is-selected');
      item.append(select);

      if (!['personal', 'work'].includes(String(workspace.name).toLowerCase())) {
        const actions = element('div', 'tma-workspace-actions');
        const rename = element('button', 'tma-icon-button', 'Rename');
        rename.type = 'button';
        rename.dataset.action = 'rename-workspace';
        rename.dataset.id = workspace.id;
        const remove = element('button', 'tma-icon-button tma-danger-action', 'Delete');
        remove.type = 'button';
        remove.dataset.action = 'delete-workspace';
        remove.dataset.id = workspace.id;
        actions.append(rename, remove);
        item.append(actions);
      }
      list.append(item);
    });
  };

  const renderTasks = () => {
    const list = root.querySelector('#tma-task-list');
    list.replaceChildren();
    if (!state.tasks.length) {
      list.append(element('li', 'tma-empty-state', 'No tasks match these filters. Add a task or adjust your search.'));
      return;
    }

    state.tasks.forEach((task) => {
      const item = element('li', 'tma-task-card');
      const meta = element('div', 'tma-task-meta');
      const priority = element('span', `tma-badge tma-badge--${task.priority}`, task.priority);
      const status = element('span', `tma-task-status tma-task-status--${task.status}`, task.status.replace('-', ' '));
      meta.append(priority, status);
      const title = element('h3', 'tma-task-title', task.title);
      const description = element('p', 'tma-task-description', task.description || 'No description provided.');
      const dueDate = element('p', 'tma-task-due', task.due_date ? `Due ${task.due_date}` : 'No due date');
      const actions = element('div', 'tma-task-actions');
      const edit = element('button', 'tma-button tma-button--quiet', 'Edit');
      edit.type = 'button';
      edit.dataset.action = 'edit-task';
      edit.dataset.id = task.id;
      const remove = element('button', 'tma-button tma-button--quiet tma-danger-action', 'Delete');
      remove.type = 'button';
      remove.dataset.action = 'delete-task';
      remove.dataset.id = task.id;
      actions.append(edit, remove);
      item.append(meta, title, description, dueDate, actions);
      list.append(item);
    });
  };

  const renderActivity = () => {
    const list = root.querySelector('#tma-activity-list');
    list.replaceChildren();
    if (!state.activity.length) {
      list.append(element('li', 'tma-empty-state', 'Activity will appear here as you work.'));
      return;
    }

    state.activity.forEach((entry) => {
      const item = element('li', 'tma-activity-item');
      item.append(
        element('span', 'tma-activity-dot'),
        element('span', 'tma-activity-description', entry.description),
        element('time', 'tma-activity-time', entry.created_at || '')
      );
      list.append(item);
    });
  };

  const loadWorkspaces = async () => {
    setStatus('Loading workspaces…');
    try {
      const payload = await request('/workspaces');
      state.workspaces = Array.isArray(payload.workspaces) ? payload.workspaces : [];
      if (!state.workspaces.some((workspace) => Number(workspace.id) === state.selectedWorkspaceId)) {
        state.selectedWorkspaceId = state.workspaces.length ? Number(state.workspaces[0].id) : null;
      }
      renderWorkspaceList();
      if (state.selectedWorkspaceId) {
        await Promise.all([loadTasks(), loadActivity()]);
      } else {
        state.tasks = [];
        state.activity = [];
        renderTasks();
        renderActivity();
      }
      setStatus('');
    } catch (error) {
      handleError(error);
    }
  };

  const loadTasks = async () => {
    if (!state.selectedWorkspaceId) return;
    const requestId = ++state.taskRequest;
    const query = new URLSearchParams({
      workspace_id: String(state.selectedWorkspaceId),
      search: root.querySelector('#tma-task-search').value.trim(),
      status: root.querySelector('#tma-task-status-filter').value,
      priority: root.querySelector('#tma-task-priority-filter').value,
      sort: root.querySelector('#tma-task-sort').value,
      order: root.querySelector('#tma-task-order').value,
    });

    try {
      const payload = await request(`/tasks?${query.toString()}`);
      if (requestId !== state.taskRequest) return;
      state.tasks = Array.isArray(payload.tasks) ? payload.tasks : [];
      renderTasks();
    } catch (error) {
      if (requestId === state.taskRequest) handleError(error);
    }
  };

  const loadActivity = async () => {
    if (!state.selectedWorkspaceId) return;
    const requestId = ++state.activityRequest;
    try {
      const payload = await request(`/activity?workspace_id=${encodeURIComponent(state.selectedWorkspaceId)}`);
      if (requestId !== state.activityRequest) return;
      state.activity = Array.isArray(payload.activity) ? payload.activity : [];
      renderActivity();
    } catch (error) {
      if (requestId === state.activityRequest) handleError(error);
    }
  };

  const createWorkspace = async (event) => {
    event.preventDefault();
    const input = root.querySelector('#tma-workspace-name');
    const submit = event.currentTarget.querySelector('[type="submit"]');
    if (!input.reportValidity()) return;
    submit.disabled = true;
    try {
      const payload = await request('/workspaces', {
        method: 'POST',
        body: JSON.stringify({ name: input.value.trim() }),
      });
      input.value = '';
      state.selectedWorkspaceId = Number(payload.workspace.id);
      await loadWorkspaces();
      setStatus('Workspace created.');
    } catch (error) {
      handleError(error);
    } finally {
      submit.disabled = false;
    }
  };

  const openWorkspaceDialog = (workspace) => {
    const dialog = root.querySelector('#tma-workspace-dialog');
    const form = root.querySelector('#tma-workspace-edit-form');
    form.reset();
    form.dataset.workspaceId = workspace.id;
    root.querySelector('#tma-workspace-edit-name').value = workspace.name;
    dialog.showModal();
    root.querySelector('#tma-workspace-edit-name').focus();
  };

  const saveWorkspace = async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    const input = root.querySelector('#tma-workspace-edit-name');
    if (!form.reportValidity()) return;

    try {
      await request(`/workspaces/${encodeURIComponent(form.dataset.workspaceId)}`, {
        method: 'PUT',
        body: JSON.stringify({ name: input.value.trim() }),
      });
      root.querySelector('#tma-workspace-dialog').close();
      await loadWorkspaces();
      setStatus('Workspace renamed.');
    } catch (error) {
      handleError(error);
    }
  };

  const confirmAction = (message) => new Promise((resolve) => {
    const dialog = root.querySelector('#tma-confirm-dialog');
    root.querySelector('#tma-confirm-message').textContent = message;
    state.confirmResolver = resolve;
    dialog.showModal();
    root.querySelector('[data-action="accept-confirmation"]').focus();
  });

  const openTaskDialog = (task = null) => {
    const dialog = root.querySelector('#tma-task-dialog');
    const form = root.querySelector('#tma-task-form');
    form.reset();
    delete form.dataset.taskId;
    root.querySelector('#tma-dialog-heading').textContent = task ? 'Edit task' : 'New task';
    if (task) {
      root.querySelector('#tma-task-title').value = task.title || '';
      root.querySelector('#tma-task-description').value = task.description || '';
      root.querySelector('#tma-task-priority').value = task.priority || 'medium';
      root.querySelector('#tma-task-status').value = task.status || 'todo';
      root.querySelector('#tma-task-due-date').value = task.due_date || '';
      form.dataset.taskId = task.id;
    }
    dialog.showModal();
    root.querySelector('#tma-task-title').focus();
  };

  const saveTask = async (event) => {
    event.preventDefault();
    const form = event.currentTarget;
    if (!form.reportValidity()) return;
    const taskId = form.dataset.taskId;
    const payload = {
      workspace_id: state.selectedWorkspaceId,
      title: root.querySelector('#tma-task-title').value.trim(),
      description: root.querySelector('#tma-task-description').value.trim(),
      priority: root.querySelector('#tma-task-priority').value,
      status: root.querySelector('#tma-task-status').value,
      due_date: root.querySelector('#tma-task-due-date').value,
    };

    try {
      await request(taskId ? `/tasks/${encodeURIComponent(taskId)}` : '/tasks', {
        method: taskId ? 'PUT' : 'POST',
        body: JSON.stringify(payload),
      });
      root.querySelector('#tma-task-dialog').close();
      await Promise.all([loadTasks(), loadActivity()]);
      setStatus(taskId ? 'Task updated.' : 'Task created.');
    } catch (error) {
      handleError(error);
    }
  };

  const handleClick = async (event) => {
    const button = event.target.closest('button[data-action]');
    if (!button || !root.contains(button)) return;
    const id = Number(button.dataset.id);

    try {
      switch (button.dataset.action) {
        case 'cancel-task':
          root.querySelector('#tma-task-dialog').close();
          break;
        case 'cancel-workspace':
          root.querySelector('#tma-workspace-dialog').close();
          break;
        case 'cancel-confirmation':
          root.querySelector('#tma-confirm-dialog').close('cancel');
          break;
        case 'accept-confirmation':
          root.querySelector('#tma-confirm-dialog').close('confirm');
          break;
        case 'select-workspace':
          state.selectedWorkspaceId = id;
          renderWorkspaceList();
          await Promise.all([loadTasks(), loadActivity()]);
          break;
        case 'edit-task': {
          const task = state.tasks.find((item) => Number(item.id) === id);
          if (task) openTaskDialog(task);
          break;
        }
        case 'delete-task':
          if (!(await confirmAction('Delete this task? This action cannot be undone.'))) return;
          await request(`/tasks/${encodeURIComponent(id)}`, { method: 'DELETE' });
          await Promise.all([loadTasks(), loadActivity()]);
          setStatus('Task deleted.');
          break;
        case 'rename-workspace': {
          const workspace = state.workspaces.find((item) => Number(item.id) === id);
          if (!workspace) return;
          openWorkspaceDialog(workspace);
          break;
        }
        case 'delete-workspace':
          if (!(await confirmAction('Delete this workspace and all its tasks and activity? This action cannot be undone.'))) return;
          await request(`/workspaces/${encodeURIComponent(id)}`, { method: 'DELETE' });
          if (state.selectedWorkspaceId === id) state.selectedWorkspaceId = null;
          await loadWorkspaces();
          setStatus('Workspace deleted.');
          break;
      }
    } catch (error) {
      handleError(error);
    }
  };

  function debounce(callback, delay) {
    let timer;
    return (...args) => {
      window.clearTimeout(timer);
      timer = window.setTimeout(() => callback(...args), delay);
    };
  }

  buildLayout();
  loadWorkspaces();
});

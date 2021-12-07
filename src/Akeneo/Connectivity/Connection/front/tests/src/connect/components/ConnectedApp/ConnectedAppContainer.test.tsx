import React, {useState} from 'react';
import '@testing-library/jest-dom/extend-expect';
import {act, screen, waitFor} from '@testing-library/react';
import {renderWithProviders} from '../../../../test-utils';
import {ConnectedAppContainer} from '@src/connect/components/ConnectedApp/ConnectedAppContainer';
import {ConnectedAppSettings} from '@src/connect/components/ConnectedApp/ConnectedAppSettings';
import {ConnectedAppPermissions} from '@src/connect/components/ConnectedApp/ConnectedAppPermissions';
import userEvent from '@testing-library/user-event';
import usePermissionsFormProviders from '@src/connect/hooks/use-permissions-form-providers';
import {NotificationLevel, NotifyContext} from '@src/shared/notify';
import {PermissionFormProvider} from '@src/shared/permission-form-registry';
import {PermissionsByProviderKey} from '@src/model/Apps/permissions-by-provider-key';

beforeEach(() => {
    window.sessionStorage.clear();
    jest.clearAllMocks();
});

// to make Tab usable with jest
type EntryCallback = (entries: {isIntersecting: boolean}[]) => void;
let entryCallback: EntryCallback | undefined = undefined;
const intersectionObserverMock = (callback: EntryCallback) => ({
    observe: jest.fn(() => (entryCallback = callback)),
    unobserve: jest.fn(),
});
window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);

const notify = jest.fn();

jest.mock('@src/connect/components/ConnectedApp/ConnectedAppSettings', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ConnectedAppSettings'),
    ConnectedAppSettings: jest.fn(() => null),
}));

type ConnectedAppPermissionsProps = {
    providers: PermissionFormProvider<any>[];
    setProviderPermissions: (providerKey: string, providerPermissions: object) => void;
    permissions: PermissionsByProviderKey;
};
jest.mock('@src/connect/components/ConnectedApp/ConnectedAppPermissions', () => ({
    ...jest.requireActual('@src/connect/components/ConnectedApp/ConnectedAppPermissions'),
    ConnectedAppPermissions: jest.fn(({setProviderPermissions}: ConnectedAppPermissionsProps) => {
        const handleClick = () => {
            setProviderPermissions('providerKey1', {view: {all: true, identifiers: []}});
            setProviderPermissions('providerKey1bis', {view: {all: false, identifiers: ['code1bis']}});
            setProviderPermissions('providerKey2', {view: {all: false, identifiers: ['code2A', 'code2B']}});
        };

        return (
            <div data-testid='set-permissions' onClick={handleClick}>
                connected-app-permissions-tab-component
            </div>
        );
    }),
}));

jest.mock('@src/connect/hooks/use-permissions-form-providers', () => ({
    __esModule: true,
    default: jest.fn(() => [null, {}, jest.fn()]),
}));

const connectedApp = {
    id: '12345',
    name: 'App A',
    scopes: ['scope1', 'scope2'],
    connection_code: 'some_connection_code',
    logo: 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
    author: 'Author A',
    user_group_name: 'app_123456abcde',
    categories: ['e-commerce', 'print'],
    certified: false,
    partner: null,
};

test('The connected app container renders without permissions tab', () => {
    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [[], {}, jest.fn()]);

    renderWithProviders(<ConnectedAppContainer connectedApp={connectedApp} />);

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).not.toBeInTheDocument();
    expect(ConnectedAppSettings).toHaveBeenCalledWith({connectedApp: connectedApp}, {});
    expect(ConnectedAppPermissions).not.toHaveBeenCalled();
});

test('The connected app container renders with permissions tab', () => {
    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];
    const mockedPermissions = {
        providerKey1: {
            view: {
                all: true,
                identifiers: [],
            },
        },
        providerKey2: {
            view: {
                all: false,
                identifiers: ['codeA'],
            },
        },
    };

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => [
        mockedProviders,
        mockedPermissions,
        jest.fn(),
    ]);

    renderWithProviders(<ConnectedAppContainer connectedApp={connectedApp} />);

    assertPageHeader();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.settings')
    ).toBeInTheDocument();
    expect(
        screen.queryByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
    ).toBeInTheDocument();
    expect(ConnectedAppSettings).toHaveBeenCalledWith(
        {
            connectedApp: connectedApp,
        },
        {}
    );
    expect(ConnectedAppPermissions).not.toHaveBeenCalled();

    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
        );
    });

    expect(ConnectedAppPermissions).toHaveBeenCalledWith(
        expect.objectContaining({
            providers: mockedProviders,
            permissions: mockedPermissions,
        }),
        {}
    );
});

test('The connected app container saves permissions', async () => {
    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => {
        const [permissions, setPermissions] = useState({});

        return [mockedProviders, permissions, setPermissions];
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppContainer connectedApp={connectedApp} />
        </NotifyContext.Provider>
    );

    await waitFor(() => {
        expect(ConnectedAppSettings).toHaveBeenCalled();
    });

    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();

    navigateToPermissionsAndFillTheFormAndSave();

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(1);
    });

    expect(notify).toHaveBeenCalledWith(
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.success'
    );
    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();
    expect(mockedProviders[0].save).toHaveBeenCalledWith(connectedApp.user_group_name, {
        view: {all: true, identifiers: []},
    });
    expect(mockedProviders[1].save).toHaveBeenCalledWith(connectedApp.user_group_name, {
        view: {all: false, identifiers: ['code2A', 'code2B']},
    });
});

test('The connected app container notifies errors when saving permissions', async () => {
    const mockedProviders = [
        {
            key: 'providerKey1',
            label: 'Provider1',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn().mockRejectedValue('some error occured'),
            loadPermissions: jest.fn(),
        },
        {
            key: 'providerKey2',
            label: 'Provider2',
            renderForm: jest.fn(),
            renderSummary: jest.fn(),
            save: jest.fn(),
            loadPermissions: jest.fn(),
        },
    ];

    (usePermissionsFormProviders as jest.Mock).mockImplementation(() => {
        const [permissions, setPermissions] = useState({});

        return [mockedProviders, permissions, setPermissions];
    });

    renderWithProviders(
        <NotifyContext.Provider value={notify}>
            <ConnectedAppContainer connectedApp={connectedApp} />
        </NotifyContext.Provider>
    );

    await waitFor(() => {
        expect(ConnectedAppSettings).toHaveBeenCalled();
    });

    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();

    navigateToPermissionsAndFillTheFormAndSave();

    await waitFor(() => {
        expect(notify).toHaveBeenCalledTimes(2);
    });

    expect(notify).toHaveBeenNthCalledWith(
        1,
        NotificationLevel.ERROR,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.save_permissions_error.description',
        {
            titleMessage:
                'akeneo_connectivity.connection.connect.connected_apps.edit.flash.save_permissions_error.title?entity=Provider1',
        }
    );
    expect(notify).toHaveBeenNthCalledWith(
        2,
        NotificationLevel.SUCCESS,
        'akeneo_connectivity.connection.connect.connected_apps.edit.flash.success'
    );
    expect(screen.queryByText('pim_common.entity_updated')).toBeInTheDocument();
});

const assertPageHeader = () => {
    expect(screen.queryByText('pim_menu.tab.connect')).toBeInTheDocument();
    expect(screen.queryByText('pim_menu.item.connected_apps')).toBeInTheDocument();
    expect(screen.queryAllByText('App A')).toHaveLength(2);
    expect(screen.queryByText('pim_common.save')).toBeInTheDocument();
};

const navigateToPermissionsAndFillTheFormAndSave = () => {
    // switch to "permissions" tab
    act(() => {
        userEvent.click(
            screen.getByText('akeneo_connectivity.connection.connect.connected_apps.edit.tabs.permissions')
        );
    });

    expect(screen.queryByText('pim_common.entity_updated')).not.toBeInTheDocument();

    // set some permissions (fill the form)
    act(() => {
        userEvent.click(screen.getByTestId('set-permissions'));
    });

    expect(screen.queryByText('pim_common.entity_updated')).toBeInTheDocument();

    // click on save button
    act(() => {
        userEvent.click(screen.getByText('pim_common.save'));
    });
};

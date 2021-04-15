import {isSameAsDefaultFiltersValues} from '@src/webhook/model/EventSubscriptionLogFilters';
import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';

test('it compares successfully the default filters values', () => {
    expect(
        isSameAsDefaultFiltersValues({
            levels: [
                EventSubscriptionLogLevel.INFO,
                EventSubscriptionLogLevel.NOTICE,
                EventSubscriptionLogLevel.WARNING,
                EventSubscriptionLogLevel.ERROR,
            ],
            text: '',
            dateTime: {},
        })
    ).toBeTruthy();
});

test('it fails to compare modified filters values with the default ones', () => {
    expect(
        isSameAsDefaultFiltersValues({
            levels: [EventSubscriptionLogLevel.INFO],
            text: '',
            dateTime: {},
        })
    ).toBeFalsy();

    expect(
        isSameAsDefaultFiltersValues({
            levels: [
                EventSubscriptionLogLevel.INFO,
                EventSubscriptionLogLevel.NOTICE,
                EventSubscriptionLogLevel.WARNING,
                EventSubscriptionLogLevel.ERROR,
            ],
            text: 'search value',
            dateTime: {},
        })
    ).toBeFalsy();
});
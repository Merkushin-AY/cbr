new Vue({
    el: '#app',
    vuetify: new Vuetify(),
    data: () => ({
        date: null,
        maxDate: null,
        menu: false,
        currencies: [],
        currency: '',
        baseCurrency: '',

        dateRules: [
            v => !!v || 'Это обязательное поле',
        ],
        currencyRules: [
            v => !!v || 'Это обязательное поле',
        ],

        apiError: '',
        mainRate: '',
        diffRate: '',
        diffRateIsPositive: true
    }),
    watch: {
    },
    methods: {

        // Получение последней даты для которой у cbr есть данные
        getLastDate: function () {
            fetch('/api?action=getLastDate',
                {
                    cache: 'force-cache'
                })
                .then(response => response.json())
                .then(result => {
                    this.date = result.data;
                    this.maxDate = result.data;
                })
                .catch(e => {
                    console.error('Catched:', e);
                    this.apiError = 'Что-то пошло не так. Обратите внимание, что на для корректной работы на сервере должен быть установлен SOAP';
                });
        },

        // Получение списка валют
        getCurrencies: function () {
            fetch('/api?action=getCurrencies',
                {
                    cache: 'force-cache'
                })
                .then(response => response.json())
                .then(result => {
                    result.data.forEach((item) => {
                        if (item.VcharCode && item.Vcode) {
                            this.currencies.push({
                                text: item.VcharCode.trim(),
                                value: item.Vcode.trim(),
                            })
                        }
                    });
                })
                .catch(e => {
                    console.error('Catched:', e);
                    this.apiError = 'Что-то пошло не так. Обратите внимание, что на для корректной работы на сервере должен быть установлен SOAP';
                });
        },

        // Отчистка вывода валют
        cleanRates: function () {
            this.mainRate = '';
            this.diffRate = '';
        },

        // Получение курса и его разницы с предыдущим днем
        getExchangeRate: function (date, code, baseCode = '') {
            this.cleanRates();

            fetch(`/api?action=getExchangeRate&date=${date}&code=${code}&baseCode=${baseCode}`, {
                cache: 'force-cache'
            })
                .then(response => {
                    return response.json()
                })
                .then(result => {
                    if (result.status && result.data?.[1]?.Vcurs  && result.data?.[0]?.Vcurs ) {
                        this.diffRate = result.data[1].Vcurs - result.data[0].Vcurs;
                        this.diffRateIsPositive = this.diffRate > 0;
                        this.diffRate = this.diffRate.toFixed(4);

                        this.mainRate = result.data[1].Vcurs.toFixed(4);
                    } else {
                        this.apiError = result.error;
                    }
                })
                .catch(e => {
                    console.error('Catched:', e);
                    this.apiError = 'Что-то пошло не так. Обратите внимание, что на для корректной работы на сервере должен быть установлен SOAP';
                });
        },

        submit: function () {
            this.apiError = '';
            if (this.$refs.form.validate()) {
                this.getExchangeRate(this.date, this.currency, this.baseCurrency)
            }

        }
    },

    mounted: function () {
        this.getLastDate();
        this.getCurrencies();
    }

})
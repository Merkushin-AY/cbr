<?php ?>
<div id="app">
    <v-app>
        <v-main>
            <v-container>
                <h1>
                    Получение курса валют
                </h1>
                <v-form @submit.prevent="submit" ref="form">
                    <div>
                        <v-menu
                            ref="menu"
                            v-model="menu"
                            :close-on-content-click="false"
                            transition="scale-transition"
                            offset-y
                            min-width="auto"
                            required
                        >
                            <template v-slot:activator="{ on, attrs }">
                                <v-text-field
                                    v-model="date"
                                    label="Дата"
                                    prepend-icon="mdi-calendar"
                                    readonly
                                    v-bind="attrs"
                                    v-on="on"
                                    :rules="dateRules"
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                v-model="date"
                                :max="maxDate"
                                no-title
                                scrollable
                            ></v-date-picker>
                        </v-menu>
                    </div>
                    <div class="mt-4">
                        <v-select
                            v-model="currency"
                            :items="currencies"
                            item-text="text"
                            item-value="value"
                            label="Валюта"
                            single-line
                            required
                            :rules="currencyRules"
                        ></v-select>
                    </div>

                    <div class="mt-4">
                        <v-select
                            v-model="baseCurrency"
                            :items="currencies"
                            item-text="text"
                            item-value="value"
                            label="Базовая валюта (по умолчанию: RUB)"
                            single-line
                            :clearable="true"
                        ></v-select>
                    </div>

                    <div class="apiError mt-4" v-show="apiError.length" v-html="apiError"></div>




                    <div class="mt-4">
                        <v-btn
                            class="mr-4"
                            type="submit"
                        >
                            Получить курс
                        </v-btn>
                    </div>
                </v-form>



                <div class="mt-4">
                    <div class="d-flex justify-sm-center">
                        <div class="rate">
                            <div class="rate_main" v-show="mainRate.length">
                                {{mainRate}}
                            </div>
                            <div class="rate_diff" v-show="diffRate.length" :class="{'green--text': diffRateIsPositive}">
                                {{diffRate}}
                            </div>
                        </div>
                    </div>
                </div>

            </v-container>
        </v-main>
    </v-app>
</div>
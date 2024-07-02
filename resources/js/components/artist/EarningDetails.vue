<template>
    <v-card class="p-3">
        <v-card-title>
            <v-icon color="primary" x-large class="mr-3"
                >$vuetify.icons.currency-usd</v-icon
            >
            {{ $t("Earnings") }}
            <v-spacer></v-spacer>
            <v-spacer></v-spacer>
            <div class="total-earnings__value price bold success--text">
                {{ price(artist.funds) + defaultCurrency.symbol }}
            </div>
        </v-card-title>
        <v-card-text>
            <div class="title">
                {{ $t("Details") }}
            </div>
            <v-divider></v-divider>
            <div class="details">
                <v-container>
                    <v-row>
                        <v-col cols="12" lg="6">
                            <v-card>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12">
                                            <v-card-title>
                                                {{ $t("Sales") }}
                                                <v-spacer></v-spacer>
                                                <v-spacer></v-spacer>
                                                <div
                                                    class="total-earnings__value bold price small success--text"
                                                >
                                                    {{
                                                        price(
                                                            totalSalesProfit
                                                        ) +
                                                            defaultCurrency.symbol
                                                    }}
                                                </div>
                                            </v-card-title>

                                            <v-divider></v-divider>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-simple-table>
                                                <template v-slot:default>
                                                    <thead>
                                                        <tr>
                                                            <th
                                                                class="text-left"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "Product"
                                                                    )
                                                                }}
                                                            </th>
                                                            <th
                                                                class="text-left"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "License"
                                                                    )
                                                                }}
                                                            </th>
                                                            <th
                                                                class="text-left"
                                                            >
                                                                {{
                                                                    $t("Price")
                                                                }}
                                                            </th>
                                                            <th
                                                                class="text-left"
                                                            >
                                                                {{
                                                                    $t("Earned")
                                                                }}
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                            v-for="(sale,
                                                            n) in artist
                                                                .sales"
                                                            :key="n"
                                                        >
                                                            <td>
                                                                <v-list-item>
                                                                    <v-list-item-avatar
                                                                        width="35"
                                                                        height="35"
                                                                        rounded="0"
                                                                        class="asset-shadow"
                                                                    >
                                                                        <img
                                                                            :src="
                                                                                sale.cover
                                                                            "
                                                                        />
                                                                    </v-list-item-avatar>
                                                                    <v-list-item-content>
                                                                        <v-list-item-title
                                                                            >{{
                                                                                sale.itemTitle
                                                                            }}</v-list-item-title
                                                                        >
                                                                    </v-list-item-content>
                                                                </v-list-item>
                                                            </td>
                                                            <td
                                                                class="text-left"
                                                            >
                                                                {{
                                                                    sale.priceName
                                                                }}
                                                            </td>
                                                            <td
                                                                class="text-left"
                                                            >
                                                                <div
                                                                    class="price success--text bold"
                                                                >
                                                                    {{
                                                                        price(
                                                                            sale.amount
                                                                        ) +
                                                                            sale.priceSymbol
                                                                    }}
                                                                </div>
                                                            </td>
                                                            <td
                                                                class="text-left"
                                                            >
                                                                <div
                                                                    class="price success--text bold"
                                                                >
                                                                    {{
                                                                        price(
                                                                            (sale.amount *
                                                                                sale.artist_cut) /
                                                                                100
                                                                        ) +
                                                                            sale.priceSymbol
                                                                    }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </template>
                                            </v-simple-table>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card>
                        </v-col>
                        <v-col cols="12" lg="6">
                            <v-card>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12">
                                            <v-card-title>
                                                {{ $t("Royalties") }}
                                                <v-spacer></v-spacer>
                                                <v-spacer></v-spacer>
                                                <div
                                                    class="total-earnings__value price small bold success--text"
                                                >
                                                    {{
                                                        price(
                                                            totalRoyaltiesProfit
                                                        ) +
                                                            defaultCurrency.symbol
                                                    }}
                                                </div>
                                            </v-card-title>

                                            <v-divider></v-divider>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-simple-table>
                                                <template v-slot:default>
                                                    <thead>
                                                        <tr>
                                                            <th
                                                                class="text-left"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "Total Plays"
                                                                    )
                                                                }}
                                                            </th>
                                                            <th
                                                                class="text-left"
                                                            >
                                                                {{
                                                                    $t(
                                                                        "Artist Royalty"
                                                                    )
                                                                }}
                                                            </th>
                                                            <th
                                                                class="text-left"
                                                            >
                                                                {{
                                                                    $t("Total")
                                                                }}
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                            v-for="(royaltyGroup,
                                                            i) in artist
                                                                .royalties"
                                                            :key="i"
                                                        >
                                                            <td>
                                                                {{
                                                                    royaltyGroup.total_royalties
                                                                }}
                                                            </td>
                                                            <td>
                                                                {{
                                                                    price(
                                                                        royaltyGroup.price
                                                                    ) +
                                                                        defaultCurrency.symbol
                                                                }}
                                                                {{
                                                                    "(" +
                                                                        $t(
                                                                            "for each 100 play"
                                                                        ) +
                                                                        ")"
                                                                }}
                                                            </td>
                                                            <td>
                                                                <div
                                                                    class="price success--text bold"
                                                                >
                                                                    {{
                                                                        price(
                                                                            royaltyGroup.total_royalties *
                                                                                (royaltyGroup.price /
                                                                                    100)
                                                                        ) +
                                                                            defaultCurrency.symbol
                                                                    }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </template>
                                            </v-simple-table>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card>
                        </v-col>
                        <v-col cols="12" lg="6">
                            <v-card>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12">
                                            <v-card-title>
                                                {{ $t("Additional Pay") }}
                                                <v-spacer></v-spacer>
                                                <v-spacer></v-spacer>
                                                <div
                                                    class="total-earnings__value price small bold success--text"
                                                >
                                                    {{
                                                        totalAdditionalPay
                                                        +
                                                        defaultCurrency.symbol
                                                    }}
                                                </div>
                                            </v-card-title>
                                            <v-divider></v-divider>
                                        </v-col>
                                        <v-col cols="6" sm="6">
                                            <template>
                                                <v-text-field
                                                    :label="$t('Price')"
                                                    v-model="additional_pay.price"
                                                    hint="Important: the amount should be in cents ( 1$ = 100 )"
                                                    type="number"
                                                ></v-text-field>


                                            </template>
                                        </v-col>
                                        <v-col cols="6" sm="6">
                                            <template>
                                                <v-text-field
                                                    :label="$t('Description')"
                                                    v-model="additional_pay.description"
                                                    type="text"
                                                ></v-text-field>

                                            </template>
                                        </v-col>
                                        <v-col cols="12" sm="12">
                                            <template>
                                                <v-btn
                                                    @click="storeAdditionalPay()">
                                                    ADD
                                                </v-btn>
                                            </template>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-simple-table>
                                                <template v-slot:default>
                                                    <thead>
                                                        <tr>
                                                            <!-- <th class="text-left">
                                                                {{
                                                                    $t(
                                                                        "Id"
                                                                    )
                                                                }}
                                                            </th> -->
                                                            <th class="text-left">
                                                                {{
                                                                    $t("Price")
                                                                }}
                                                            </th>
                                                            <th class="text-left">
                                                                {{
                                                                    $t("Description")
                                                                }}
                                                            </th>
                                                            <!-- <th class="text-left">
                                                                {{
                                                                    $t("Action")
                                                                }}
                                                            </th> -->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                            v-for="(additional_pay,
                                                            i) in artist
                                                                .additional_pay"
                                                            :key="i"
                                                        >
                                                            <!-- <td>
                                                                {{
                                                                    additional_pay.id
                                                                }}
                                                            </td> -->

                                                            <td>
                                                                <div
                                                                    class="price success--text bold"
                                                                >
                                                                    {{
                                                                        (additional_pay.price/100) +
                                                                            defaultCurrency.symbol
                                                                    }}
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div
                                                                    class="bold"
                                                                >
                                                                    {{
                                                                        additional_pay.description
                                                                    }}
                                                                </div>
                                                            </td>
                                                            <!-- <td>
                                                                <a @click="deleteAdditionalPay(additional_pay.id, index)">
                                                                    Delete
                                                                </a>
                                                            </td> -->
                                                        </tr>
                                                    </tbody>
                                                </template>
                                            </v-simple-table>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card>
                        </v-col>
                        <v-col cols="12">
                            <v-card>
                                <v-card-title>
                                    {{ $t("Previous Payouts") }}
                                </v-card-title>
                                <v-simple-table
                                    v-if="payedPayouts && payedPayouts.length"
                                >
                                    <template v-slot:default>
                                        <thead>
                                            <tr>
                                                <th class="text-left">
                                                    Payout N°
                                                </th>
                                                <th class="text-left">
                                                    Date
                                                </th>
                                                <th class="text-left">
                                                    Amount
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="payout in payedPayouts"
                                                :key="payout.id"
                                            >
                                                <td class="text-left">
                                                    {{ payout.id }}
                                                </td>
                                                <td class="text-left">
                                                    {{
                                                        moment(
                                                            payout.created_at
                                                        ).format("ll")
                                                    }}
                                                </td>
                                                <td
                                                    class="text-left bold error--text"
                                                >
                                                    {{
                                                        -price(payout.amount) +
                                                            defaultCurrency.symbol
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </template>
                                </v-simple-table>
                                <div class="text-center" v-else>
                                    {{ $t("No previous payouts") }}
                                </div>
                            </v-card>
                        </v-col>
                        <v-col cols="12">
                            <v-card>
                                <v-card-title>
                                    {{ $t("Summary") }}
                                </v-card-title>
                                <v-simple-table>
                                    <template v-slot:default>
                                        <thead>
                                            <tr>
                                                <th class="text-center"></th>
                                                <th class="text-center"></th>
                                                <th class="text-right">
                                                    {{ $t("Account Funds") }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td
                                                    class="text-right bold success--text"
                                                >
                                                    {{
                                                        price(
                                                            artist.funds
                                                        ) +
                                                            defaultCurrency.symbol
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </template>
                                </v-simple-table>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-container>
            </div>
        </v-card-text>
    </v-card>
</template>

<script>
import Billing from '@mixins/billing/billing'
export default {
    props: ['artist'],
    mixins: [Billing],
    data: function() {
       return {
          additional_pay: {}
        }
  },
    computed: {
        totalSalesProfit() {
            return this.artist.sales.reduce(
                (acc, val) => acc + (val.amount * val.artist_cut) / 100,
                0
            );
        },
        totalRoyaltiesProfit() {
            return this.artist.royalties.reduce(
                (acc, val) => acc + val.total_royalties * (val.price / 100),
                0
            );
        },
        payedPayouts() {
            return this.artist.payouts.filter(
                payout => payout.status === "payed"
            );
        },
        totalAdditionalPay(){
            let total_price = 0;
            if(this.artist.additional_pay.length > 0){
                this.artist.additional_pay.map(function(value, key) {
                    total_price += value.price / 100;
                });
            }
            return total_price;
        }
    },
    methods: {
        getAdditionalPay(){
            console.log('artist_id', this.artist)
        },
        deleteAdditionalPay(id, index){
            var formData = new FormData();
            axios.post("/api/admin/additional-pay/"+id, formData, {
                        headers: {
                            "Content-Type": "multipart/form-data"
                        }
                    })
                    .then(res => {
                        // this.artist = res.data;
                        this.artist.additional_pay.splice(index, 1);
                        this.$emit("updated");
                        this.isLoading = false;
                    })
                    .catch(e => {
                        this.isLoading = false;
                        this.errors = e.response.data.errors;
                        // this.$notify({
                        //     group: "foo",
                        //     type: "error",
                        //     title: this.$t("Error"),
                        //     text: Object.values(e.response.data.errors).join(
                        //         "<br />"
                        //     )
                        // });
                    });
        },
        storeAdditionalPay(){
            var formData = new FormData();
            formData.append("price", this.additional_pay.price || "");
            formData.append("description", this.additional_pay.description || "");
            formData.append("artist_id", this.artist.id || "");
            axios.post("/api/admin/additional-pay", formData, {
                        headers: {
                            "Content-Type": "multipart/form-data"
                        }
                    })
                    .then(res => {
                        this.artist.additional_pay.push(res.data);
                        // this.artist.funds += this.additional_pay.price;
                        this.additional_pay.price = null;
                        this.additional_pay.description = null;
                        // this.artist = res.data;
                        this.$emit("updated");
                        this.isLoading = false;
                    })
                    .catch(e => {
                        this.isLoading = false;
                        this.errors = e.response.data.errors;
                        // this.$notify({
                        //     group: "foo",
                        //     type: "error",
                        //     title: this.$t("Error"),
                        //     text: Object.values(e.response.data.errors).join(
                        //         "<br />"
                        //     )
                        // });
                    });
        }

    },
    beforeMount(){
        this.getAdditionalPay()
    },
};
</script>

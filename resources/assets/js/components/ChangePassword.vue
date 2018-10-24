<template>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Change Password</h5>
                <form @submit.prevent="change">
                    <div class="form-group">
                        <input autofocus v-model="values.old_password" type="password"
                               class="form-control"
                               @input="errors.old_password = null"
                               :class="{'is-invalid': errors.old_password}"
                               placeholder="Enter Old Password">
                        <span v-if="errors.old_password" class="invalid-feedback">{{errors.old_password}}</span>
                    </div>
                    <div class="form-group">
                        <input v-model="values.password" type="password"
                               class="form-control"
                               @input="errors.password = null"
                               :class="{'is-invalid': errors.password}"
                               placeholder="Enter New Password">
                        <span v-if="errors.password" class="invalid-feedback">{{errors.password}}</span>
                    </div>
                    <div class="form-group">
                        <input v-model="values.password_confirmation" type="password"
                               class="form-control"
                               @input="errors.password_confirmation = null"
                               :class="{'is-invalid': errors.password_confirmation}"
                               placeholder="Confirm New Password">
                        <span v-if="errors.password_confirmation" class="invalid-feedback">{{errors.password_confirmation}}</span>
                    </div>
                    <button href="#" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        data() {
            return {
                values: {
                    old_password: null,
                    password: null,
                    password_confirmation: null
                },
                errors: {
                    old_password: null,
                    password: null,
                    password_confirmation: null
                }
            }
        },
        methods: {
            change() {
                // Validate and show errors
                let hasErrors = false;
                if (!this.values.old_password) {
                    this.errors.old_password = 'You have to enter old password';
                    hasErrors = true;
                }
                if (!this.values.password) {
                    this.errors.password = 'You must enter the new password';
                    hasErrors = true;
                }
                if (!this.values.password_confirmation) {
                    this.errors.password_confirmation = 'You must confirm your new password';
                    hasErrors = true;
                }
                if (this.values.password != this.values.password_confirmation) {
                    this.errors.password_confirmation = 'Password mismatch';
                    hasErrors = true;
                }
                if (this.values.password && this.values.password.length < 6) {
                    this.errors.password = 'Password is too short';
                }
                // Try to send the data only if no validation errors
                if (hasErrors) {
                    return;
                }

                axios.post('/update_password', {
                    old_password: this.values.old_password,
                    password: this.values.password,
                    password_confirmation: this.values.password_confirmation
                }).then(response => {
                    if (response.data.status == 'success') {
                        for (let prop in this.values) {
                            this.values[prop] = null;
                        }
                        this.$snotify.success('Password Updated Successfuly');
                    }
                }).catch(error => {
                    if (error.response && error.response.status == 422 && error.response.data.status == 'incorrect_old_password') {
                        this.errors.old_password = 'This password is incorrect';
                        return;
                    }
                    // General message in case something unexpected happened
                    this.$snotify.error('Something went wrong');
                });
            }
        }
    }
</script>

<style scoped>

</style>

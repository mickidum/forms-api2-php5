<template>
	<div class="inner" v-if="settingsItem">
		<span class="close" @click="closeModal">&times;</span>
		<form @submit.prevent="saveSettings" class="pure-form pure-form-stacked">
				<!-- {{settingsItem}} -->

				<!-- ITEMS NAMES BLOCK -->
				<template v-if="settingsItem.items_names">
					<div class="heading">
						<span @click="toggleEditItems" class="pure-button button-xsmall button-warning">{{!editItems ? 'open' : 'close'}}</span>&nbsp;
						<strong>Items Names</strong>&nbsp;
						<span>( change items titles )</span>
					</div>
					<template v-if="editItems">
						<div class="validate-container">
							<div v-for="item in settingsItem.items_names" class="items-names">
								<label class="settings-input-item">
							      			{{item.name}}
									<input type="text" v-model="item.title">
								</label>
							</div>
						</div>
					</template>
				</template>
				
				<!-- VALIDATION BLOCK -->
				<template v-if="settingsItem.validation">
					<div class="heading">
						<span @click="toggleEditValidation" class="pure-button button-xsmall button-warning">{{!editValidation ? 'open' : 'close'}}</span>&nbsp;
						<strong>Validation</strong>&nbsp;
						<span>( set validation rules )</span>
					</div>
					<template v-if="editValidation">
						<div class="items-names">
							

							<div class="validate-container">
									<span 
										style="display: inline-block; margin: 10px 0;" 
										@click="settingsItem.validation.validate = !settingsItem.validation.validate" 
										v-model="settingsItem.validation.validate" 
										:class="['pure-button button-small', settingsItem.validation.validate ? 'button-error' : 'button-success']"
										>
										{{!settingsItem.validation.validate ? 'Click for validate' : 'Click for disable validation'}}
									</span>
									<br>
								<template v-if="settingsItem.items_names">
									<strong>Items to validate</strong>
									<div v-for="item in settingsItem.items_names" class="validate-items">
										<label 
											v-if="item.name !== 'date'"
											:class="['custom-ch', checkValidate(item.name) ? 'custom-ch-checked' : null]"
										>
										<input type="checkbox" v-model="settingsItem.validation.validate_items" :value="item.name">
										</label>
										<span>{{item.name == 'date' ? null : item.title}}</span>
									</div>
								</template>
							</div>

							<div class="validate-container" v-if="settingsItem.validation.messages">
								<strong>Messages</strong>
								<div v-for="(item, key) in settingsItem.validation.messages" class="items-names">
									<label class="settings-input-item">
								      			{{key}}
										<input type="text" v-model="settingsItem.validation.messages[key]">
									</label>
								</div>
							</div>

						</div>
					</template>
				</template>
				
				<!-- EMAIL SENDING BLOCK -->
				<template v-if="settingsItem.mail">
					<div class="heading">
						<span @click="toggleMailSending" class="pure-button button-xsmall button-warning">{{!editMailSending ? 'open' : 'close'}}</span>&nbsp;
						<strong>Mail Sending</strong>&nbsp;
						<span>( set email rules )</span>
					</div>
					<template v-if="editMailSending">
						<div class="validate-container">
							<div v-for="(item, key) in settingsItem.mail" class="items-names">
								<span 
									style="margin: 10px 0;" 
									v-if="key === 'send'"
									@click="settingsItem.mail.send = !settingsItem.mail.send" 
									v-model="settingsItem.mail.send" 
									:class="['pure-button button-small', settingsItem.mail.send ? 'button-error' : 'button-success']"
									>
									{{!settingsItem.mail.send ? 'Click for enable mail' : 'Click for disable mail'}}
								</span>
								<label v-else class="settings-input-item">
							      {{key}}
									<input type="text" v-model="settingsItem.mail[key]">
								</label>
							</div>
						</div>
					</template>
				</template>

				
				<!-- <pre>{{settingsItem}}</pre> -->
			<p>
				<button type="submit" class="pure-button pure-button-primary">Save Settings</button>&nbsp;
				<button @click.prevent="closeModal" class="pure-button button-secondary pure-button-primary">Cancel</button>
			</p>
		</form>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				editItems: false,
				editValidation: false,
				editMailSending: false
			}
		},
		props: [
			'settingsItem',
		],
		computed: {
			
		},
		methods: {
			closeModal() {
				this.$emit('closeModal');
			},
			saveSettings() {
				this.$emit('saveSettings');
			},
			toggleEditItems() {
				this.editItems = !this.editItems
			},
			toggleEditValidation() {
				this.editValidation = !this.editValidation
			},
			toggleMailSending() {
				this.editMailSending = !this.editMailSending
			},
			checkValidate(item) {
				let index = this.settingsItem.validation.validate_items.indexOf(item)
				if (index < 0) {
					return false
				}
				return true
			}
		}
	}
</script>
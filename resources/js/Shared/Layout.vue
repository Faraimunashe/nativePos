<template>
    <div class="min-h-screen bg-gray-100 p-6 flex flex-col">
        <NavBar :username="username" :role="role" />
        <slot />
    </div>
</template>

<script>
import NavBar from './NavBar.vue';

export default {
    components: {
        NavBar
    },
    computed: {
        username() {
            return this.$page.props.auth.user.name
        }
    }
};
</script>

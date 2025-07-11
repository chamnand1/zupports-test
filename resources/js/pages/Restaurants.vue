<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import RestaurantList from '../components/RestaurantList.vue';
import { useForm } from '@inertiajs/vue3';
import { Restaurant } from '@/types';

/**
 * Default keyword to be used if no keyword is provided in the query string.
 */
const DEFAULT_KEYWORD = 'Bang Sue';

/**
 * Props interface received from the backend (Inertia).
 */
interface Props {
  keyword: string;
  restaurants: Restaurant[];
}

// Receive props
const props = defineProps<Props>();

/**
 * Inertia form helper for keyword submission.
 */
const form = useForm<{ keyword: string }>({
  keyword: DEFAULT_KEYWORD,
});

/**
 * Local keyword ref bound to input.
 * Renamed to avoid conflict with props.keyword.
 */
const localKeyword = ref(DEFAULT_KEYWORD);

/**
 * Computed list of restaurants from props.
 */
const restaurants = computed(() => props.restaurants as Restaurant[]);

/**
 * On mounted:
 * - Check URL query string for `keyword`
 * - If found, set localKeyword and form.keyword to it
 * - Otherwise, fallback to DEFAULT_KEYWORD
 */
onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const paramKeyword = urlParams.get('keyword')?.trim();

  if (paramKeyword && paramKeyword.length > 0) {
    localKeyword.value = paramKeyword;
    form.keyword = paramKeyword;
  } else {
    localKeyword.value = DEFAULT_KEYWORD;
  }
});

/**
 * Perform search action:
 * - Sync form keyword with local input
 * - Send GET request to backend search route
 */
const search = () => {
  form.keyword = localKeyword.value;
  form.get(route('restaurants.search'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      console.log("Search successful!", restaurants.value);
    },
    onError: (errors: any) => {
      console.error("Error during search:", errors);
    },
  });
};
</script>

<template>
  <div class="container mt-5">
    <!-- Page title -->
    <h2>Search Restaurants</h2>

    <!-- Search form -->
    <form @submit.prevent="search">
      <div class="input-group mb-3">
        <input
          type="text"
          class="form-control"
          v-model="localKeyword"
          placeholder="Enter keyword..."
        />
        <button class="btn btn-primary" type="submit">Search</button>
      </div>
    </form>

    <!-- Restaurant list -->
    <RestaurantList :restaurants="restaurants" />
  </div>
</template>

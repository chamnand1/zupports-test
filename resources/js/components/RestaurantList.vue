<script setup lang="ts">
import { Restaurant } from '@/types';

/**
 * Props definition.
 * - restaurants: list of Restaurant objects to display
 */
const props = defineProps<{
  restaurants: Restaurant[];
}>();
</script>

<template>
  <div>
    <!-- Empty state when there are no restaurants -->
    <div v-if="props.restaurants.length === 0" class="text-center text-muted py-5">
      <i class="bi bi-search fs-1 d-block mb-3"></i>
      <p class="lead">No restaurants found. Try another keyword.</p>
    </div>

    <!-- Restaurants grid -->
    <div class="row g-4">
      <!-- Loop through each restaurant and render card -->
      <div
        v-for="r in props.restaurants"
        :key="r.placeId"
        class="col-12 col-md-6 col-lg-4"
      >
        <div class="card h-100 shadow-sm">
          <!-- Restaurant photo -->
          <img
            v-if="r.photoUrl"
            :src="r.photoUrl"
            alt="Restaurant photo"
            class="card-img-top"
            style="height: 200px; object-fit: cover"
          />
          
          <!-- Card body -->
          <div class="card-body d-flex flex-column">
            <!-- Restaurant name -->
            <h5 class="card-title">{{ r.name }}</h5>
            
            <!-- Restaurant address -->
            <p class="card-text text-muted mb-3">
              <i class="bi bi-geo-alt-fill me-1 text-danger"></i>
              {{ r.address }}
            </p>
            
            <!-- Link to Google Maps -->
            <a
              :href="`https://www.google.com/maps/place/?q=place_id:${r.placeId}`"
              target="_blank"
              class="btn btn-outline-primary btn-sm mt-auto"
            >
              <i class="bi bi-map"></i> View on Google Map
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<h2>Calendly Schedule Extractor</h2>
    <form action="/extract" method="post">
        <div class="mb-3">
            <label for="calendly_url" class="form-label">Calendly Link</label>
            <input type="url" class="form-control" id="calendly_url" name="calendly_url" required placeholder="https://calendly.com/username/event">
        </div>
        <button type="submit" class="btn btn-primary">Extract Information</button>
    </form>
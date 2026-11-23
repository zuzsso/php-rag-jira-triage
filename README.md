# JiRag. Framework-Independent PHP 8.3 & MariaDB RAG Triage Engine


JiRag (a _portmanteau_ of Jira and RAG, short for Retrieval-Augmented Generation) is a self-contained, command-line Retrieval-Augmented Generation (RAG) proof of concept developed using PHP 8.3, MariaDB 11.7 with native HNSW vector indexing, and Ollama for locally hosted embedding and language models.

The project demonstrates how conventional software engineering techniques and widely available technologies can be used to build AI-powered applications without requiring Python microservices, dedicated vector databases, paid AI services or specialised orchestration frameworks such as LangChain.

Given a bug report or incident description, JiRag converts the input into a 768-dimensional embedding, performs semantic similarity searches against approximately 16,000 indexed document chunks extracted from historical Jira bug reports, and uses the retrieved information to generate a structured triage report with references to the original tickets.

The application deliberately focuses on a limited dataset and a command-line interface. Its purpose is to demonstrate the underlying architecture, integration techniques and separation of responsibilities rather than provide a production-ready ticket management solution.

JiRag is an experimental implementation intended to demonstrate RAG techniques and AI integration. Retrieved results and generated conclusions should not be interpreted as authoritative technical recommendations without verification against the original Jira issues.

# Core Architectural Pillars
1. Zero-Framework PHP 8.3 & SOLID Dependency Inversion: Built cleanly without bloated AI wrapper frameworks or Python sidecars, isolating LLM backends behind LLMProviderInterface (implemented by OllamaLocalServerLlm for local inference and swappable via PHP-DI 7 to remote Ollama or OpenAI without modifying domain logic).
1. Native InnoDB HNSW Indexing & ANN Search: Approximately 16,000 Jira knowledge chunks are stored in MariaDB 11.7 using native VECTOR(768) columns and HNSW vector indexing. Similarity retrieval uses VEC_DISTANCE_COSINE() to identify potentially relevant historical issues without requiring a separate vector database.
1. End-to-End Batched ETL & Ingestion Pipeline: Automated extraction from Atlassian’s public REST API, markup sanitization, overlapping text chunking (DocumentChunker), and batched 768-dimensional vector embedding generation into transactional InnoDB storage.
1. Retrieval Filtering & LLM Guardrails: Combines a configurable cosine-distance threshold (currently 0.55) with input-length restrictions, structured prompt boundaries, behavioural instructions and JSON Schema-constrained responses. The similarity threshold was selected heuristically based on observed retrieval results and may require further tuning. These measures aim to reduce irrelevant retrievals and unsupported generated responses, rather than guarantee their elimination.

# Setup 
## Create the .env File
Create an .env file on the project's dir root. It should contain: 
```
DB_HOST=db
DB_NAME=jirag
DB_USER=jirag_user
DB_PASS=jirag_password
```

## Build the project
```
$ cd <PROJECT_DIR>
$ ./bash_scripts/docker/docker_up.sh 
$ ./bash_scripts/docker/docker_composer_install.sh

```

The Docker environment initialises the MariaDB database and creates the required tables, including the table used to store document chunks and their vector embeddings.

The document database is initially empty. It is populated during the subsequent ingestion step, which processes the supplied Jira fixtures and generates the corresponding embeddings through Ollama.

## Create the fixtures file (optional)
This step retrieves the 250 most recently resolved Jira bug reports from Atlassian's public REST API and generates a JSON fixtures file containing the information required to populate JiRag's document database and generate the corresponding vector embeddings.

For the purposes of this project, this step has already been completed, and the generated fixtures file is included in the repository. There is no need to retrieve the tickets again unless you wish to refresh the dataset with more recently resolved issues.

Please avoid executing this script unnecessarily to minimise the load on Atlassian's public servers. The retrieval process uses paginated requests, fetching tickets in small batches with a short delay between requests to reduce its impact on the API.

Launch it with the following command: 

`$ <PROJECT_DIR>/bash_scripts/ingest/ingest_fixtures_and_create_embeddings.sh`

The command will fail if the database already contains records. Pass the `--force` flag to truncate the existing table and create new embeddings

The generated file is saved at:

`<PROJECT_DIR>/resources/fixtures/atlassian_jira_tickets.json`

This file serves as the source dataset for populating JiRag's document database and subsequently generating the vector embeddings used by the RAG system.

## Ingest data fixtures and generate vector embeddings
Once the JSON fixtures file is available (`<PROJECT_DIR>/resources/fixtures/atlassian_jira_tickets.json`), its contents must be ingested into the local database, and the corresponding vector embeddings generated to enable semantic search within JiRag.

This process uses a locally hosted Ollama instance to generate the embeddings. Alternative embedding providers can also be used, provided they implement the same application interface and support the required embedding functionality.

The ingestion process is expected to create approximately 16,000 database records and may take several minutes to complete, depending on the available hardware and the performance of the embedding model.

To start the ingestion process, execute the following command from the project's root directory:

`$ <PROJECT_DIR>/bash_scripts/ingest/ingest_fixtures_and_create_embeddings.sh`

Once completed, the database will contain the indexed Jira content and its associated vector embeddings, ready to be used by the RAG retrieval pipeline.

## Triage (examples of use 1/4)
```
$ cd <PROJECT_DIR>/bash_scripts/triage 
$ ./triage.sh  "Agile board fails to load and throws a 500 NullPointerException after upgrading Jira Software."
```

Result:

```
====================================================================
   JIRAG SEMANTIC TRIAGE REPORT (33902.7 ms)
====================================================================
   Grounded   : YES (Verified in Historical Jira Data)
   Component  : Agile board
   Confidence : 50%
--------------------------------------------------------------------
   ANALYSIS          
   ========          
      500 NullPointerException after upgrading Jira Software

   RECOMMENDED ACTION
   ================== 
      Check for any recent changes or updates to the Agile board
      component
--------------------------------------------------------------------
   HNSW NEAREST NEIGHBOR CITATIONS:
      * JRASERVER-79409 [Fixed]

        After switching to OpenSearch on Jira, the JQL query "Team is/is not EMPTY" gives "An
        unknown error occurred while trying to perform a search"

        Chunked texts:
             - [Cosine Distance: 0.27304006808197] errors. No Jira restart or reindex is required.
            Engineer Note: This issue has now been fixed for the next release of Jira Software:
            11.3.10 

             - [Cosine Distance: 0.28259449302171] thout errors. No Jira restart or reindex is
            required. Engineer Note: This issue has now been fixed for the next release of Jira
            Software: 11.3.10

             - [Cosine Distance: 0.29395986816091] without errors. No Jira restart or reindex is
            required. Engineer Note: This issue has now been fixed for the next release of Jira
            Software: 11.3.10

====================================================================
```

## Triage (examples of use 2/4)
```
$ cd <PROJECT_DIR>/bash_scripts/triage
$ ./triage.sh  "Sprint report burndown chart shows incorrect remaining estimate values when subtasks are moved."
```

Result:
```
====================================================================
   JIRAG SEMANTIC TRIAGE REPORT (30216.8 ms)
====================================================================
   Grounded   : YES (Verified in Historical Jira Data)
   Component  : Sprint report burndown chart
   Confidence : 50%
--------------------------------------------------------------------
   ANALYSIS          
   ========          
      Incorrect remaining estimate values when subtasks are moved

   RECOMMENDED ACTION
   ================== 
      Verify the subtask movement logic and update the chart
      accordingly
--------------------------------------------------------------------
   HNSW NEAREST NEIGHBOR CITATIONS:
      * JRACLOUD-98514 [Fixed]

        Plans API returns filtered results for admin users instead of all plans

        Chunked texts:
             - [Cosine Distance: 0.40825441010678] ining the full list of plan IDs. Engineer Note:
            The bug fix has now been completed. Please reopen ticket if the problem persists.

             - [Cosine Distance: 0.41709614761123] taining the full list of plan IDs. Engineer Note:
            The bug fix has now been completed. Please reopen ticket if the problem persists.

      * JRACLOUD-99194 [Fixed]

        Intermittent behaviour with Σ Original Estimate/Σ Remaining Estimate issue calculation

        Chunked texts:
             - [Cosine Distance: 0.41890944175842] ased on the broader impact described above.
            Engineer Note: A fix for this bug has been made and will be rolled out to each region
            today.

             - [Cosine Distance: 0.42065518815211] cribed above. Engineer Note: A fix for this bug
            has been made and will be rolled out to each region today.

====================================================================
```

## Triage (examples of use 3/4)
```
$ cd <PROJECT_DIR>/bash_scripts/triage
$ ./triage.sh  "Background re-indexing gets stuck at 99% and locks agile board configuration changes."
```

Result:

```
====================================================================
   JIRAG SEMANTIC TRIAGE REPORT (18984.1 ms)
====================================================================
   Grounded   : YES (Verified in Historical Jira Data)
   Component  : Agile Board
   Confidence : 33%
--------------------------------------------------------------------
   ANALYSIS          
   ========          
      Background re-indexing issue

   RECOMMENDED ACTION
   ================== 
      Reopen or track as a new regression
--------------------------------------------------------------------
   HNSW NEAREST NEIGHBOR CITATIONS:
      * JRACLOUD-86912 [Fixed]

        Status on issue card not updating and requires page refresh

        Chunked texts:
             - [Cosine Distance: 0.32936157567806] fore - the board used to update correctly. It is
            disruptive to daily work, so it would be good to have this reopened or tracked as a new
            regression. 

             - [Cosine Distance: 0.3333692511725] - the board used to update correctly. It is
            disruptive to daily work, so it would be good to have this reopened or tracked as a new
            regression.

             - [Cosine Distance: 0.34706954997938] ok at the board Expected Results: the card is
            immediately shown in the column matching its current status, with no manual refresh.
            Actual Results: the card remains in the old status column. Refreshing the page once
            frequently does not help; several refreshes are needed before the board renders the
            correct state. This did not happen before - the board used to update correctly. It is
            disruptive to daily work, so it would be good to have this reopened or tracked as a new
            regression.

====================================================================
```

## Triage (examples of use 4/4)
```
$ cd <PROJECT_DIR>/bash_scripts/triage
$ ./triage.sh  "Android mobile dashboard seems odd. The status is not showing the correct label after the last release."
```

Result:

```
====================================================================
   JIRAG SEMANTIC TRIAGE REPORT (15240.7 ms)
====================================================================
   Grounded   : YES (Verified in Historical Jira Data)
   Component  : Android mobile dashboard
   Confidence : 50%
--------------------------------------------------------------------
   ANALYSIS          
   ========          
      Status label issue after last release

   RECOMMENDED ACTION
   ================== 
      Verify status label changes after last release
--------------------------------------------------------------------
   HNSW NEAREST NEIGHBOR CITATIONS:
      * JRACLOUD-98211 [Fixed]

        The parent status validator and separation of duties conditions do not support case changes
        in status names

        Chunked texts:
             - [Cosine Distance: 0.38070961437864] is means that status renames will no longer cause
            the conditions to stop working. 

             - [Cosine Distance: 0.38921518593432] conditions. This means that status renames will
            no longer cause the conditions to stop working.

             - [Cosine Distance: 0.39080480097088] itions. This means that status renames will no
            longer cause the conditions to stop working.

====================================================================

```

## Architecture & Design Q&A

### 1. Why did you select this particular embedding model and chunking strategy?
* **Embedding Model (`nomic-embed-text`, 768 dimensions):** It provides strong semantic retrieval on technical English while running completely locally inside Docker with minimal memory footprint (~274 MB). Its 768-dimensional output also matches OpenAI's `text-embedding-3-small` (when configured with `dimensions: 768`), allowing provider switches without altering the MariaDB `VECTOR(768)` schema.
* **Chunking Strategy (`DocumentChunker` with overlapping windows):** Real Jira tickets contain verbose stack traces, reproduction steps, and engineer resolution comments. Slicing tickets into bounded character windows with word-boundary alignment and overlap (`120–150` chars) prevents semantic dilution across long logs while ensuring key context is never severed mid-sentence at a chunk boundary.

### 2. How do you preserve relationships between retrieved chunks and their original Jira tickets?
Each row in `jira_knowledge_chunks` stores structured relational metadata (`ticket_key`, `title`, `resolution`, `chunk_index`) alongside the raw text slice and its `VECTOR(768)` column inside InnoDB. When `VectorRepository` executes an Approximate Nearest Neighbor (ANN) query against the HNSW index, it hydrates an immutable `EmbeddedTicketChunkDTO` containing both the cosine distance and the parent ticket's identifiers—allowing the CLI/UI to display direct, verifiable links back to `https://atlassian.com{ticket_key}`.

### 3. How do you separate ingestion, vector generation, retrieval, and LLM inference?
Each stage is isolated into single-responsibility components:
* **Extraction & Ingestion (`bin/fetch_jira.php`, `bin/ingest.php`, `DocumentChunker`):** Runs offline as an idempotent CLI pipeline decoupled from runtime queries.
* **Vector Generation & Chat Inference (`LLMProviderInterface` -> `OllamaLocalServerLlm`):** Encapsulates all external HTTP/JSON communication with the model server.
* **Vector Retrieval (`VectorRepository`):** Encapsulates pure PDO SQL queries against MariaDB's HNSW `VECTOR INDEX` (`VEC_DISTANCE_COSINE`), returning domain DTOs without knowing which LLM generated the vectors.
* **RAG Orchestration (`TriageService`):** Coordinates embedding -> retrieval -> guardrailed prompt construction -> DTO hydration independently of whether the entry point is a CLI command (`bin/triage.php`) or an HTTP controller.

### 4. How do you distinguish useful retrieval results from irrelevant ones?
MariaDB's HNSW index always returns the top-*K* nearest neighbors (`ORDER BY VEC_DISTANCE_COSINE(...) ASC LIMIT :limit`), even if a query has nothing to do with Jira. `VectorRepository` enforces a strict cosine-distance ceiling (`$maxDistance = 0.55`, where `0.0` is identical and `2.0` is opposite). Any chunk exceeding `0.55` is discarded, and confidence scores are computed deterministically from the top match's cosine distance rather than relying on subjective LLM self-assessment.

### 5. How do you prevent the LLM from presenting unsupported conclusions as facts?
JiRag enforces multi-layered deterministic and behavioral guardrails:
* **Pre-Prompt Short-Circuiting:** If no retrieved chunk satisfies `cosineDistance <= 0.55`, `TriageService` bypasses the LLM entirely and returns a deterministic `grounded: false` result.
* **Prompt Boundary Isolation:** Retrieved ticket text is quarantined inside `<context>` XML tags and user input inside `<bug_report>` tags, paired with system-level behavioral rules (`$guardrailedSystemPrompt`) instructing the model to rely *only* on `<context>` and never invent ticket keys.
* **Schema-Constrained Output:** Chat completions enforce a strict JSON Schema (`$jsonSchema`) and low temperature, forcing structured output into `TriageResultDTO` alongside verifiable source citations.

### 6. How easily could a different dataset, embedding provider, or LLM be integrated?
* **Swapping LLMs or Embedding Providers:** Because `TriageService` and `bin/ingest.php` depend exclusively on `LLMProviderInterface`, switching to a remote Ollama server or cloud OpenAI requires only implementing the interface (e.g., `OpenAiClient`), updating a single binding in `config/container.php` (PHP-DI 7), and running `php bin/ingest.php --force` to re-index the local JSON fixture.
* **Swapping Datasets:** `bin/ingest.php` reads a simple normalized JSON array (`ticket_key`, `title`, `resolution`, `full_text`). Any internal support tickets, Confluence exports, or runbooks formatted into that schema can be ingested with zero changes to the retrieval or triage engine.

### 7. Why does JiRag use a command-line interface rather than a web interface?

JiRag deliberately uses a command-line interface to keep the proof of concept focused on its core functionality: document ingestion, vector embedding generation, semantic retrieval and LLM-assisted ticket triage.

The application follows a service-oriented design in which the underlying business logic is independent of the entry point. The CLI invokes the same application services that could be exposed through HTTP controllers in a web-based implementation.

Adding a web interface would therefore primarily involve introducing the appropriate routing, request-handling and presentation components, without requiring changes to the underlying RAG pipeline.

The CLI approach also reduces dependencies and keeps the implementation easier to inspect, test and reproduce.

### 8. Can JiRag use alternative AI providers instead of Ollama?

Yes. JiRag uses a provider-independent LLMProviderInterface that defines the operations required by the application: single and batch embedding generation, and chat completion with optional JSON Schema-constrained output.

The current implementation, OllamaLocalServerLlm, communicates with a locally hosted Ollama instance using two independently configured models:

* Embedding model (nomic-embed-text): Generates 768-dimensional vectors for semantic retrieval.
* Generative model (llama3.2:3b): Processes retrieved context and produces structured triage reports.

Alternative providers, such as OpenAI, can be integrated by implementing LLMProviderInterface and updating the corresponding binding in the PHP-DI container. The ingestion, retrieval and triage services depend on the interface rather than the provider implementation, so their application logic does not need to change.

The two model configurations are independent. Changing the generative model does not require regenerating existing embeddings. However, changing the embedding model requires re-embedding the stored documents to maintain compatibility between document and query vectors. A different embedding dimension may also require changes to the database schema.

Ollama was selected for this proof of concept to enable local execution without requiring API credentials, paid subscriptions or reliance on external AI services.

# License
MIT